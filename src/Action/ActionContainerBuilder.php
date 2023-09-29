<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Config;

class ActionContainerBuilder
{
    private array $sharedServices = [];

    public function __construct(private readonly Config $config)
    {
    }

    public function build(string $actionId): ActionContainer
    {
        $container = ActionContainer::build(
            $actionId,
            $this->config->actionContainerCacheDir,
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
