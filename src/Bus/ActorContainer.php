<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\DI\Container;
use Duyler\DI\ContainerConfig;
use Duyler\EventBus\BusConfig;

class ActorContainer extends Container
{
    public function __construct(
        public readonly string $actorId,
        public readonly BusConfig $config,
        public readonly string $scope = 'common',
    ) {
        $containerConfig = new ContainerConfig();
        $containerConfig->withBind($config->bind);
        $containerConfig->withProvider($config->providers);

        foreach ($config->definitions as $definition) {
            $containerConfig->withDefinition($definition);
        }

        parent::__construct($containerConfig);
    }
}
