<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Collection\CompleteActionCollection;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Action;

class ActionContainerProvider
{
    /** @var array<string, object>  */
    private array $sharedServices = [];
    /** @var array<string, string>  */
    private array $bind = [];

    public function __construct(
        private readonly BusConfig $config,
        private readonly ActionContainerCollection $containerCollection,
        private readonly CompleteActionCollection $completeActionCollection,
        private readonly ActionContainerBind $actionContainerBind,
    ) {}

    public function get(Action $action): ActionContainer
    {
        $container = $this->prepareContainer($action->id);

        $container->bind($action->bind);
        $container->addProviders($action->providers);

        /** @psalm-suppress MixedArgumentTypeCoercion */
        $completeActions = $this->completeActionCollection->getAllByArray($action->required->getArrayCopy());

        foreach ($completeActions as $completeAction) {
            $bind = $this->actionContainerBind->get($completeAction->action->id);
            $container->bind($bind);
        }

        $this->containerCollection->save($container);

        return $container;
    }

    private function prepareContainer(string $actionId): ActionContainer
    {
        $container = new ActionContainer(
            $actionId,
            $this->config,
        );

        foreach ($this->sharedServices as $service) {
            $container->set($service);
        }

        $container->bind($this->bind);

        return $container;
    }

    /** @param array<string, string> $bind  */
    public function addSharedService(object $service, array $bind = []): void
    {
        $this->sharedServices[$service::class] = $service;
        $this->bind = $bind + $this->bind;
    }
}
