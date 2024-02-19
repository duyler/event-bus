<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Action\ActionContainerProvider;
use Duyler\EventBus\Action\ActionRequiredIterator;
use Duyler\EventBus\Bus\Bus;
use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\SubscriptionCollection;
use Duyler\EventBus\Contract\ActionSubstitutionInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Exception\ActionAlreadyDefinedException;
use InvalidArgumentException;

readonly class ActionService
{
    public function __construct(
        private ActionCollection $actionCollection,
        private ActionContainerProvider $actionContainerProvider,
        private ActionSubstitutionInterface $actionSubstitution,
        private SubscriptionCollection $subscriptionCollection,
        private Bus $bus,
    ) {}

    public function addAction(Action $action): void
    {
        if ($this->actionCollection->isExists($action->id)) {
            throw new ActionAlreadyDefinedException($action->id);
        }

        foreach ($action->required as $subject) {
            if (false === $this->actionCollection->isExists($subject)) {
                $this->throwNotRegistered($subject);
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
        // TODO Add check if action exists
        $action = $this->actionCollection->get($actionId);

        $this->bus->doAction($action);
    }

    public function getById(string $actionId): Action
    {
        return $this->actionCollection->get($actionId);
    }

    public function getByContract(string $contract): array
    {
        return $this->actionCollection->getByContract($contract);
    }

    public function actionIsExists(string $actionId): bool
    {
        return $this->actionCollection->isExists($actionId);
    }

    /** @param  Action[] $actions */
    public function collect(iterable $actions): void
    {
        foreach ($actions as $action) {
            $requiredIterator = new ActionRequiredIterator($action->required, $actions);

            foreach ($requiredIterator as $subject) {
                if (false === array_key_exists($subject, $actions)) {
                    $this->throwNotRegistered($subject);
                }
            }

            $this->actionCollection->save($action);
        }
    }

    private function throwNotRegistered(string $subject): never
    {
        throw new InvalidArgumentException('Required action ' . $subject . ' not registered in the bus');
    }

    public function addSharedService(object $service, array $bind = []): void
    {
        $this->actionContainerProvider->addSharedService($service, $bind);
    }

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
        $this->actionCollection->remove($actionId);
        $this->subscriptionCollection->remove($actionId);
    }
}
