<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\DI\Attribute\Finalize;
use Duyler\EventBus\Bus\CompleteAction;
use Duyler\EventBus\Dto\Result;

#[Finalize(method: 'reset')]
class CompleteActionStorage
{
    /**
     * @var array<string, CompleteAction>
     */
    private array $data = [];

    /**
     * @var array<string, CompleteAction>
     */
    private array $byTypeId = [];

    public function save(CompleteAction $completeAction): void
    {
        $this->data[$completeAction->action->getId()] = $completeAction;

        $type = $completeAction->action->getTypeId();

        if (null !== $type) {
            $this->byTypeId[$type] = $completeAction;
        }
    }

    /**
     * @return array<string, CompleteAction>
     */
    public function getAllByTypeArray(array $array): array
    {
        return array_intersect_key($this->byTypeId, array_flip($array));
    }

    /**
     * @return array<string, CompleteAction>
     */
    public function getAllByArray(array $array): array
    {
        return array_intersect_key($this->data, array_flip($array));
    }

    public function getResult(string $actionId): Result
    {
        return $this->data[$actionId]->result;
    }

    public function get(string $actionId): CompleteAction
    {
        return $this->data[$actionId];
    }

    public function isExists(string $actionId): bool
    {
        return array_key_exists($actionId, $this->data);
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
        $this->byTypeId = [];
    }

    public function remove(string $actionId): void
    {
        unset($this->data[$actionId]);
    }
}
