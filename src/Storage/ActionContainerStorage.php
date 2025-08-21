<?php

namespace Duyler\EventBus\Storage;

use Duyler\DI\Attribute\Finalize;
use Duyler\EventBus\Bus\ActionContainer;

use function array_flip;
use function array_intersect_key;

#[Finalize(method: 'reset')]
class ActionContainerStorage
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
            $container->finalize();
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

    public function remove(string $actionId): void
    {
        unset($this->data[$actionId]);
    }
}
