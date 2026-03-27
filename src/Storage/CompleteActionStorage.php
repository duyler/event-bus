<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\DI\Attribute\Finalize;
use Duyler\EventBus\Bus\CompleteAction;
use Duyler\EventBus\Dto\Result;

use function array_flip;
use function array_intersect_key;
use function array_key_exists;

#[Finalize(method: 'reset')]
class CompleteActionStorage
{
    /**
     * @var array<string, CompleteAction>
     */
    private array $data = [];

    public function save(CompleteAction $completeAction): void
    {
        $this->data[$completeAction->action->getId() . '.' . $completeAction->scope] = $completeAction;
    }

    /**
     * @param array<array-key, string> $array
     * @return array<string, CompleteAction>
     */
    public function getAllByArray(array $array, string $scope = 'common'): array
    {
        $withScope = [];

        foreach ($array as $actionId) {
            $withScope[] = $actionId . '.' . $scope;
        }

        return array_intersect_key($this->data, array_flip($withScope));
    }

    public function getResult(string $actionId, string $scope = 'common'): Result
    {
        return $this->data[$actionId . '.' . $scope]->result;
    }

    public function get(string $actionId, string $scope = 'common'): CompleteAction
    {
        return $this->data[$actionId . '.' . $scope];
    }

    public function isExists(string $actionId, string $scope = 'common'): bool
    {
        return array_key_exists($actionId . '.' . $scope, $this->data);
    }

    /**
     * @return array<string, CompleteAction>
     */
    public function getAll(): array
    {
        return $this->data;
    }

    public function reset(): void
    {
        $this->data = [];
    }

    public function remove(string $actionId, string $scope = 'common'): void
    {
        unset($this->data[$actionId . '.' . $scope]);
    }
}
