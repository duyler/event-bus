<?php

namespace Duyler\EventBus\Storage;

use Duyler\DI\Attribute\Finalize;
use Duyler\EventBus\Bus\ActorContainer;

#[Finalize(method: 'reset')]
class ActorContainerStorage
{
    /**
     * @var array<string, ActorContainer>
     */
    private array $data = [];

    public function save(ActorContainer $container): void
    {
        $this->data[$container->actorId . '.' . $container->scope] = $container;
    }

    public function get(string $actorId, string $scope = 'common'): ActorContainer
    {
        return $this->data[$actorId . '.' . $scope];
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

    public function isExists(string $actorId, string $scope = 'common'): bool
    {
        return isset($this->data[$actorId . '.' . $scope]);
    }

    public function remove(string $actorId, string $scope = 'common'): void
    {
        unset($this->data[$actorId . '.' . $scope]);
    }
}
