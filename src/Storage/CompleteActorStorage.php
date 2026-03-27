<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\DI\Attribute\Finalize;
use Duyler\EventBus\Bus\CompleteActor;
use Duyler\EventBus\Dto\Result;

use function array_flip;
use function array_intersect_key;
use function array_key_exists;

#[Finalize(method: 'reset')]
class CompleteActorStorage
{
    /**
     * @var array<string, CompleteActor>
     */
    private array $data = [];

    public function save(CompleteActor $completeActor): void
    {
        $this->data[$completeActor->actor->getId() . '.' . $completeActor->scope] = $completeActor;
    }

    /**
     * @param array<array-key, string> $array
     * @return array<string, CompleteActor>
     */
    public function getAllByArray(array $array, string $scope = 'common'): array
    {
        $withScope = [];

        foreach ($array as $actorId) {
            $withScope[] = $actorId . '.' . $scope;
        }

        return array_intersect_key($this->data, array_flip($withScope));
    }

    public function getResult(string $actorId, string $scope = 'common'): Result
    {
        return $this->data[$actorId . '.' . $scope]->result;
    }

    public function get(string $actorId, string $scope = 'common'): CompleteActor
    {
        return $this->data[$actorId . '.' . $scope];
    }

    public function isExists(string $actorId, string $scope = 'common'): bool
    {
        return array_key_exists($actorId . '.' . $scope, $this->data);
    }

    /**
     * @return array<string, CompleteActor>
     */
    public function getAll(): array
    {
        return $this->data;
    }

    public function reset(): void
    {
        $this->data = [];
    }

    public function remove(string $actorId, string $scope = 'common'): void
    {
        unset($this->data[$actorId . '.' . $scope]);
    }
}
