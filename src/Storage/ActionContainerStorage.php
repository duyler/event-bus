<?php

namespace Duyler\EventBus\Storage;

use Duyler\DI\Attribute\Finalize;
use Duyler\EventBus\Bus\ActionContainer;

#[Finalize(method: 'reset')]
class ActionContainerStorage
{
    /**
     * @var array<string, ActionContainer>
     */
    private array $data = [];

    public function save(ActionContainer $container): void
    {
        $this->data[$container->actionId . '.' . $container->scope] = $container;
    }

    public function get(string $actionId, string $scope = 'common'): ActionContainer
    {
        return $this->data[$actionId . '.' . $scope];
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

    public function isExists(string $actionId, string $scope = 'common'): bool
    {
        return isset($this->data[$actionId . '.' . $scope]);
    }

    public function remove(string $actionId): void
    {
        unset($this->data[$actionId]);
    }
}
