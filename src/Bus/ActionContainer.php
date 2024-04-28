<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Bus;

use Duyler\DependencyInjection\Container;
use Duyler\DependencyInjection\ContainerConfig;
use Duyler\ActionBus\BusConfig;
use Duyler\ActionBus\Enum\ResetMode;

class ActionContainer extends Container
{
    public function __construct(
        public readonly string $actionId,
        public readonly BusConfig $config,
    ) {
        $containerConfig = new ContainerConfig();
        $containerConfig->withBind($config->bind);
        $containerConfig->withProvider($config->providers);

        foreach ($config->definitions as $definition) {
            $containerConfig->withDefinition($definition);
        }

        parent::__construct($containerConfig);
    }

    public function reset(): void
    {
        if ($this->config->resetMode === ResetMode::Soft) {
            $this->softReset();
        } else {
            $this->selectiveReset();
        }
    }
}
