<?php

namespace Duyler\EventBus\Collection;

use Duyler\EventBus\Action\ActionContainer;

class ActionContainerCollection
{
    /**
     * @var array<string, ActionContainer>
     */
    private array $data = [];
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

    public function reset(): void
    {
        foreach ($this->data as $container) {
            $container->softReset();
        }
    }

    public function getAll(): array
    {
        return $this->data;
    }

    public function isExists(string $actionId): bool
    {
        return isset($this->data[$actionId]);
    }
}
