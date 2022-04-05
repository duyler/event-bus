<?php

namespace Konveyer\EventBus\Storage;

use Konveyer\EventBus\Container;

class ContainerStorage
{
    private array $containers = [];

    public function save(string $actionFullName, Container $container): void
    {
        $this->containers[$actionFullName] = $container;
    }

    public function get(string $actionFullName): Container
    {
        return $this->containers[$actionFullName];
    }
}
