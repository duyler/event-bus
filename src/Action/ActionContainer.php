<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\DependencyInjection\Container;
use Duyler\DependencyInjection\ContainerConfig;
use Duyler\EventBus\Config;

class ActionContainer extends Container
{
    public function __construct(
        public readonly string $actionId,
        public readonly Config $config,
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
