<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Action\ActionContainerProvider;
use Duyler\EventBus\Bus\ActionRequiredIterator;
use Duyler\EventBus\Bus\Bus;
use Duyler\EventBus\Collection\ActionArgumentCollection;
use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\SubscriptionCollection;
use Duyler\EventBus\Contract\ActionSubstitutionInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Exception\ActionAlreadyDefinedException;
use Duyler\EventBus\Exception\ActionNotDefinedException;
use Duyler\EventBus\Exception\CannotRequirePrivateActionException;
use Duyler\EventBus\Exception\NotAllowedSealedActionException;

readonly class ActionService
{
    public function __construct(
        private ActionCollection $actionCollection,
        private ActionContainerProvider $actionContainerProvider,
        private ActionSubstitutionInterface $actionSubstitution,
        private SubscriptionCollection $subscriptionCollection,
        private Bus $bus,
        private ActionArgumentCollection $actionArgumentCollection,
    ) {}

    public function addAction(Action $action): void
    {
        if ($this->actionCollection->isExists($action->id)) {
            throw new ActionAlreadyDefinedException($action->id);
        }

        /** @var string $subject */
        foreach ($action->required as $subject) {
            if (false === $this->actionCollection->isExists($subject)) {
                $this->throwActionNotDefined($subject);
            }

            $requiredAction = $this->actionCollection->get($subject);

            $this->checkRequiredAction($action->id, $requiredAction);
        }

        foreach ($action->alternates as $actionId) {
            if (false === $this->actionCollection->isExists($actionId)) {
                $this->throwActionNotDefined($actionId);
            }
        }

        $this->actionCollection->save($action);
    }

    public function doAction(Action $action): void
    {
        $this->addAction($action);

        $this->bus->doAction($action);
    }

    public function doExistsAction(string $actionId): void
    {
        if (false === $this->actionCollection->isExists($actionId)) {
            $this->throwActionNotDefined($actionId);
        }

        $action = $this->actionCollection->get($actionId);

        $this->bus->doAction($action);
    }

    public function getById(string $actionId): Action
    {
        if (false === $this->actionCollection->isExists($actionId)) {
            $this->throwActionNotDefined($actionId);
        }

        return $this->actionCollection->get($actionId);
    }

    /** @return array<string, Action> */
    public function getByContract(string $contract): array
    {
        return $this->actionCollection->getByContract($contract);
    }

    public function actionIsExists(string $actionId): bool
    {
        return $this->actionCollection->isExists($actionId);
    }

    /** @param array<string, Action> $actions */
    public function collect(array $actions): void
    {
        foreach ($actions as $action) {
            $requiredIterator = new ActionRequiredIterator($action->required, $actions);

            /** @var string $subject */
            foreach ($requiredIterator as $subject) {
                if (false === array_key_exists($subject, $actions)) {
                    $this->throwActionNotDefined($subject);
                }

                $requiredAction = $actions[$subject];

                $this->checkRequiredAction($action->id, $requiredAction);
            }

            foreach ($action->alternates as $actionId) {
                if (false === array_key_exists($actionId, $actions)) {
                    $this->throwActionNotDefined($actionId);
                }
            }

            $this->actionCollection->save($action);
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

    /** @param array<string, string> $bind  */
    public function addSharedService(object $service, array $bind = []): void
    {
        $this->actionContainerProvider->addSharedService($service, $bind);
    }

    /** @param array<string, object> $substitutions */
    public function addResultSubstitutions(string $actionId, array $substitutions): void
    {
        $this->actionSubstitution->addResultSubstitutions($actionId, $substitutions);
    }

    public function addHandlerSubstitution(string $actionId, string $handlerSubstitution): void
    {
        $this->actionSubstitution->addHandlerSubstitution($actionId, $handlerSubstitution);
    }

    public function removeAction(string $actionId): void
    {
        $actions = $this->actionCollection->getAll();

        foreach ($actions as $action) {
            if (in_array($actionId, $action->alternates) || in_array($actionId, $action->required->getArrayCopy())) {
                $this->removeAction($action->id);
            }
        }

        $this->actionCollection->remove($actionId);
        $this->subscriptionCollection->removeByActionId($actionId);
    }

    public function getArgument(string $actionId): object
    {
        return $this->actionArgumentCollection->get($actionId);
    }

    public function argumentIsExists(string $actionId): bool
    {
        return $this->actionArgumentCollection->isExists($actionId);
    }
}
