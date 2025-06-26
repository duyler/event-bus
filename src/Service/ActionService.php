<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Action\ActionContainerProvider;
use Duyler\EventBus\Build\ActionHandlerSubstitution;
use Duyler\EventBus\Build\ActionResultSubstitution;
use Duyler\EventBus\Build\SharedService;
use Duyler\EventBus\Bus\Action;
use Duyler\EventBus\Bus\ActionRequiredIterator;
use Duyler\EventBus\Bus\ActionRequiredMap;
use Duyler\EventBus\Bus\Bus;
use Duyler\EventBus\Bus\TaskQueue;
use Duyler\EventBus\Contract\ActionSubstitutionInterface;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Enum\TaskStatus;
use Duyler\EventBus\Exception\ActionAlreadyDefinedException;
use Duyler\EventBus\Exception\ActionNotDefinedException;
use Duyler\EventBus\Exception\ActionWithNotResolvedDependsException;
use Duyler\EventBus\Exception\CannotRequirePrivateActionException;
use Duyler\EventBus\Exception\CircularCallActionException;
use Duyler\EventBus\Exception\EventNotDefinedException;
use Duyler\EventBus\Exception\NotAllowedSealedActionException;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Storage\ActionContainerStorage;
use Duyler\EventBus\Storage\ActionStorage;
use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\EventBus\Storage\EventRelationStorage;
use Duyler\EventBus\Storage\EventStorage;
use Duyler\EventBus\Storage\TaskStorage;

readonly class ActionService
{
    public function __construct(
        private ActionStorage $actionStorage,
        private ActionContainerProvider $actionContainerProvider,
        private ActionSubstitutionInterface $actionSubstitution,
        private EventStorage $eventStorage,
        private Bus $bus,
        private ActionContainerStorage $actionContainerStorage,
        private EventRelationStorage $eventRelationStorage,
        private CompleteActionStorage $completeActionStorage,
        private ActionRequiredMap $actionRequiredMap,
        private TaskStorage $taskStorage,
        private TaskQueue $taskQueue,
    ) {}

    private function validateAction(Action $action): void
    {
        if ($this->actionStorage->isExists($action->getId())) {
            throw new ActionAlreadyDefinedException($action->getId());
        }

        /** @var string $subject */
        foreach ($action->getRequired() as $subject) {
            if (false === $this->actionStorage->isExists($subject)) {
                $this->throwActionNotDefined($subject);
            }

            $requiredAction = $this->actionStorage->get($subject);

            $this->checkRequiredAction($action->getId(), $requiredAction);
        }

        foreach ($action->getAlternates() as $actionId) {
            if (false === $this->actionStorage->isExists($actionId)) {
                $this->throwActionNotDefined($actionId);
            }
        }

        foreach ($action->getListen() as $eventId) {
            if (false === $this->eventStorage->has($eventId)) {
                $this->throwEventNotDefined($eventId, $action->getId());
            }
        }
    }

    public function doExistsAction(string $actionId): void
    {
        if (false === $this->actionStorage->isExists($actionId)) {
            $this->throwActionNotDefined($actionId);
        }

        $action = $this->actionStorage->get($actionId);

        $this->bus->doAction($action);
    }

    public function getById(string $actionId): Action
    {
        if (false === $this->actionStorage->isExists($actionId)) {
            $this->throwActionNotDefined($actionId);
        }

        return $this->actionStorage->get($actionId);
    }

    /** @return array<string, Action> */
    public function getByType(string $type): array
    {
        return $this->actionStorage->getByType($type);
    }

    public function actionIsExists(string $actionId): bool
    {
        return $this->actionStorage->isExists($actionId);
    }

    /**
     * @param array<string, Action> $actions
     */
    public function collect(array $actions): void
    {
        foreach ($actions as $action) {

            $this->checkDependsOn($action, $actions);

            $requiredIterator = new ActionRequiredIterator($action->getRequired(), $actions);

            /** @var string $subject */
            foreach ($requiredIterator as $subject) {
                if (false === array_key_exists($subject, $actions)) {
                    $this->throwActionNotDefined($subject);
                }

                $this->checkRequiredAction($action->getId(), $actions[$subject]);
            }

            foreach ($action->getAlternates() as $actionId) {
                if (false === array_key_exists($actionId, $actions)) {
                    $this->throwActionNotDefined($actionId);
                }
            }

            foreach ($action->getListen() as $eventId) {
                if (false === $this->eventStorage->has($eventId)) {
                    $this->throwEventNotDefined($eventId, $action->getId());
                }
            }

            $this->actionContainerProvider->buildContainer($action);

            $this->actionRequiredMap->create($action);
            $this->actionStorage->save($action);
        }
    }

    private function checkRequiredAction(string $subject, Action $requiredAction): void
    {
        if (in_array($subject, $requiredAction->getRequired()->getArrayCopy())) {
            throw new CircularCallActionException($subject, $requiredAction->getId());
        }

        if ($requiredAction->isPrivate()) {
            throw new CannotRequirePrivateActionException($subject, $requiredAction->getId());
        }

        if (count($requiredAction->getSealed()) > 0 && !in_array($subject, $requiredAction->getSealed())) {
            throw new NotAllowedSealedActionException($subject, $requiredAction->getId());
        }
    }

    /**
     *@param array<string, Action> $actions
     */
    private function checkDependsOn(Action $actionWithDepends, array $actions): void
    {
        $depends = [];

        foreach ($actions as $action) {
            if (null !== $action->getTypeId()) {
                $depends[$action->getTypeId()] = $action;
            }
        }

        foreach ($actionWithDepends->getDependsOn() as $typeId) {
            if (false === array_key_exists($typeId, $depends)) {
                throw new ActionWithNotResolvedDependsException($typeId, $actionWithDepends->getId());
            }
        }
    }

    private function throwActionNotDefined(string $subject): never
    {
        throw new ActionNotDefinedException($subject);
    }

    private function throwEventNotDefined(string $eventId, string $actionId): never
    {
        throw new EventNotDefinedException($eventId, $actionId);
    }

    public function addSharedService(SharedService $sharedService): void
    {
        $this->actionContainerProvider->addSharedService($sharedService);
    }

    public function addResultSubstitutions(ActionResultSubstitution $actionResultSubstitution): void
    {
        $this->actionSubstitution->addResultSubstitutions($actionResultSubstitution);
    }

    public function addHandlerSubstitution(ActionHandlerSubstitution $handlerSubstitution): void
    {
        $this->actionSubstitution->addHandlerSubstitution($handlerSubstitution);
    }

    public function addDynamicAction(Action $action): void
    {
        $this->validateAction($action);

        $this->actionRequiredMap->create($action);
        $this->actionStorage->save($action);
        $this->actionStorage->saveDynamic($action);
    }

    public function doDynamicAction(Action $action): void
    {
        $this->validateAction($action);

        $this->actionRequiredMap->create($action);
        $this->actionStorage->save($action);
        $this->actionStorage->saveDynamic($action);

        $this->bus->doAction($action);
    }

    public function removeAction(string $actionId): void
    {
        $stack = [$actionId];
        $visited = [];

        while (0 < count($stack)) {

            $currentActionId = array_pop($stack);

            if (isset($visited[$currentActionId])) {
                continue;
            }

            $visited[$currentActionId] = true;

            if (false === $this->actionStorage->isExistsDynamic($currentActionId)) {
                continue;
            }

            $action = $this->actionStorage->get($currentActionId);
            $triggeredOn = $action->getTriggeredOn();

            foreach ($triggeredOn as $triggeredActionId) {
                $triggeredAction = $this->actionStorage->get($triggeredActionId);
                $triggeredAction->removeTrigger($currentActionId, ResultStatus::Success);
                $triggeredAction->removeTrigger($currentActionId, ResultStatus::Fail);
            }

            $requiredMap = $this->actionRequiredMap->get($currentActionId);

            $this->actionRequiredMap->remove($currentActionId);

            $tasks = $this->taskStorage->getAllByActionId($currentActionId);
            foreach ($tasks as $task) {
                if ($this->taskQueue->inQueue($currentActionId)) {
                    if (TaskStatus::Primary === $task->getStatus()) {
                        $task->reject();
                    }
                }
                if (TaskStatus::Held !== $task->getStatus()) {
                    $this->bus->removeHeldTask($task->getId());
                }
            }

            IdFormatter::remove($currentActionId);

            $this->actionStorage->removeDynamic($currentActionId);
            $this->actionContainerStorage->remove($currentActionId);
            $this->eventRelationStorage->removeByActionId($currentActionId);
            if ($this->completeActionStorage->isExists($currentActionId)) {
                $this->completeActionStorage->remove($currentActionId);
            }

            foreach ($requiredMap as $subject) {
                $stack[] = $subject->getId();
            }

            $allActions = $this->actionStorage->getAll();
            $hasSameType = false;

            foreach ($allActions as $actionDepends) {
                if ($action->getTypeId() === $actionDepends->getTypeId()) {
                    $hasSameType = true;
                    break;
                }
            }

            if ($hasSameType) {
                continue;
            }

            foreach ($allActions as $actionDepends) {
                if (in_array($action->getTypeId(), $actionDepends->getDependsOn())) {
                    $stack[] = $actionDepends->getId();
                }
            }
        }
    }
}
