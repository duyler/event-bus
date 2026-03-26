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

    public function save(CompleteAction $completeAction, string $correlationId = 'common'): void
    {
        $this->data[$completeAction->action->getId() . '.' . $correlationId] = $completeAction;
    }

    /**
     * @param array<array-key, string> $array
     * @return array<string, CompleteAction>
     */
    public function getAllByArray(array $array, string $correlationId = 'common'): array
    {
        $withCorrelationId = [];

        foreach ($array as $actionId) {
            $withCorrelationId[] = $actionId . '.' . $correlationId;
        }

        return array_intersect_key($this->data, array_flip($withCorrelationId));
    }

    public function getResult(string $actionId, string $correlationId = 'common'): Result
    {
        return $this->data[$actionId . '.' . $correlationId]->result;
    }

    public function get(string $actionId, string $correlationId = 'common'): CompleteAction
    {
        return $this->data[$actionId . '.' . $correlationId];
    }

    public function isExists(string $actionId, string $correlationId = 'common'): bool
    {
        return array_key_exists($actionId . '.' . $correlationId, $this->data);
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

    public function remove(string $actionId, string $correlationId = 'common'): void
    {
        unset($this->data[$actionId . '.' . $correlationId]);
    }
}
