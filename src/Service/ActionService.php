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
use RuntimeException;
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
        if (false === $this->actionCollection->isExists($action->id)) {
            foreach ($action->required as $subject) {
                if (false === $this->actionCollection->isExists($subject)) {
                    $this->throwNotRegistered($subject);
                }
            }

            $this->actionCollection->save($action);
        }
    }

    public function doAction(Action $action): void
    {
        if (false === $action->externalAccess) {
            throw new RuntimeException('Action ' . $action->id . ' does not allow external access');
        }

        $this->addAction($action);

        $this->bus->doAction($action);
    }

    public function doExistsAction(string $actionId): void
    {
        $action = $this->actionCollection->get($actionId);

        $this->doAction($action);
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

            if (false === $this->actionCollection->isExists($action->id)) {
                $this->actionCollection->save($action);
            }
        }
    }

    private function throwNotRegistered(string $subject): never
    {
        throw new InvalidArgumentException('Required action ' . $subject . ' not registered in the bus');
    }

    public function addSharedService(object $service): void
    {
        $this->actionContainerProvider->addSharedService($service);
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
