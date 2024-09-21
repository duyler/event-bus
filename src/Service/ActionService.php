<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Action\ActionContainerProvider;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\ActionHandlerSubstitution;
use Duyler\EventBus\Build\ActionResultSubstitution;
use Duyler\EventBus\Build\SharedService;
use Duyler\EventBus\Bus\ActionRequiredIterator;
use Duyler\EventBus\Bus\Bus;
use Duyler\EventBus\Contract\ActionSubstitutionInterface;
use Duyler\EventBus\Exception\ActionAlreadyDefinedException;
use Duyler\EventBus\Exception\ActionNotDefinedException;
use Duyler\EventBus\Exception\CannotRequirePrivateActionException;
use Duyler\EventBus\Exception\EventNotDefinedException;
use Duyler\EventBus\Exception\NotAllowedSealedActionException;
use Duyler\EventBus\Storage\ActionStorage;
use Duyler\EventBus\Storage\EventStorage;
use Duyler\EventBus\Storage\TriggerStorage;

readonly class ActionService
{
    public function __construct(
        private ActionStorage $actionStorage,
        private ActionContainerProvider $actionContainerProvider,
        private ActionSubstitutionInterface $actionSubstitution,
        private TriggerStorage $triggerStorage,
        private EventStorage $eventStorage,
        private Bus $bus,
    ) {}

    public function addAction(Action $action): void
    {
        if ($this->actionStorage->isExists($action->id)) {
            throw new ActionAlreadyDefinedException($action->id);
        }

        /** @var string $subject */
        foreach ($action->required as $subject) {
            if (false === $this->actionStorage->isExists($subject)) {
                $this->throwActionNotDefined($subject);
            }

            $requiredAction = $this->actionStorage->get($subject);

            $this->checkRequiredAction($action->id, $requiredAction);
        }

        foreach ($action->alternates as $actionId) {
            if (false === $this->actionStorage->isExists($actionId)) {
                $this->throwActionNotDefined($actionId);
            }
        }

        foreach ($action->listen as $eventId) {
            if (false === $this->eventStorage->has($eventId)) {
                $this->throwEventNotDefined($eventId, $action->id);
            }
        }

        $this->actionStorage->save($action);
    }

    public function doAction(Action $action): void
    {
        $this->addAction($action);

        $this->bus->doAction($action);
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
    public function getByContract(string $contract): array
    {
        return $this->actionStorage->getByContract($contract);
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
            $requiredIterator = new ActionRequiredIterator($action->required, $actions);

            /** @var string $subject */
            foreach ($requiredIterator as $subject) {
                if (false === array_key_exists($subject, $actions)) {
                    $this->throwActionNotDefined($subject);
                }

                $this->checkRequiredAction($action->id, $actions[$subject]);
            }

            foreach ($action->alternates as $actionId) {
                if (false === array_key_exists($actionId, $actions)) {
                    $this->throwActionNotDefined($actionId);
                }
            }

            foreach ($action->listen as $eventId) {
                if (false === $this->eventStorage->has($eventId)) {
                    $this->throwEventNotDefined($eventId, $action->id);
                }
            }

            $this->actionContainerProvider->buildContainer($action);

            $this->actionStorage->save($action);
        }
    }

    private function checkRequiredAction(string $subject, Action $requiredAction): void
    {
        if ($requiredAction->private) {
            throw new CannotRequirePrivateActionException($subject, $requiredAction->id);
        }

        if (count($requiredAction->sealed) > 0 && !in_array($subject, $requiredAction->sealed)) {
            throw new NotAllowedSealedActionException($subject, $requiredAction->id);
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

    public function removeAction(string $actionId): void
    {
        $actions = $this->actionStorage->getAll();

        foreach ($actions as $action) {
            if (in_array($actionId, $action->alternates) || in_array($actionId, $action->required->getArrayCopy())) {
                $this->removeAction($action->id);
            }
        }

        $this->actionStorage->remove($actionId);
        $this->triggerStorage->removeByActionId($actionId);
    }
}
