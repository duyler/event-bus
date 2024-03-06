<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Bus\ActionContainer;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Collection\ActionContainerCollection;
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
    ) {}

    public function get(Action $action): ActionContainer
    {
        if ($this->containerCollection->isExists($action->id)) {
            if ($this->config->saveStateActionContainer) {
                return $this->containerCollection->get($action->id);
            }
        }

        return $this->prepareContainer($action);
    }

    private function prepareContainer(Action $action): ActionContainer
    {
        $container = new ActionContainer(
            $action->id,
            $this->config,
        );

        $container->bind($action->bind);
        $container->addProviders($action->providers);

        foreach ($this->sharedServices as $service) {
            $container->set($service);
        }

        $container->bind($this->bind);

        $this->containerCollection->save($container);

        return $container;
    }

    /** @param array<string, string> $bind  */
    public function addSharedService(object $service, array $bind = []): void
    {
        $this->sharedServices[$service::class] = $service;
        $this->bind = $bind + $this->bind;
    }
}
