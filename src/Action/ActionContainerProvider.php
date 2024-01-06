<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Collection\EventCollection;
use Duyler\EventBus\Config;
use Duyler\EventBus\Dto\Action;

class ActionContainerProvider
{
    private array $sharedServices = [];

    public function __construct(
        private readonly Config $config,
        private readonly ActionContainerCollection $containerCollection,
        private readonly EventCollection $eventCollection,
        private readonly ActionContainerBind $containerDefinition,
    ) {}

    public function get(Action $action): ActionContainer
    {
        $container = $this->prepareContainer($action->id);

        $container->bind($action->classMap);
        $container->addProviders($action->providers);

        $completeTasks = $this->eventCollection->getAllByArray($action->required->getArrayCopy());

        foreach ($completeTasks as $task) {
            $bind = $this->containerDefinition->get($task->action->id);
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

        return $container;
    }

    public function addSharedService(object $service): void
    {
        $this->sharedServices[] = $service;
    }
}
