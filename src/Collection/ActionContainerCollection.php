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

    /**
     * @return ActionContainer[]
     */
    public function getAllByArray(array $array): array
    {
        return array_intersect_key($this->data, array_flip($array));
    }

    public function cleanUp(): void
    {
        /** @var ActionContainer $container */
        foreach ($this->data as $container) {
            $container->softCleanUp();
        }
    }
}
