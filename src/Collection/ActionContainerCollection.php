<?php

namespace Duyler\EventBus\Collection;

use Duyler\EventBus\Action\ActionContainer;

class ActionContainerCollection extends AbstractCollection
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
