<?php

namespace Duyler\EventBus\Storage;

use Duyler\EventBus\Action\ActionContainer;

class ContainerStorage extends AbstractStorage
{
    public function save(ActionContainer $container): void
    {
        $this->data[$container->actionId] = $container;
    }

    public function get(string $actionId): ActionContainer
    {
        return $this->data[$actionId];
    }
}
