<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Collection;

use Duyler\DependencyInjection\Attribute\Finalize;
use Duyler\ActionBus\Bus\CompleteAction;
use Duyler\ActionBus\Dto\Result;

#[Finalize(method: 'reset')]
class CompleteActionCollection
{
    /**
     * @var array<string, CompleteAction>
     */
    private array $data = [];

    public function save(CompleteAction $completeAction): void
    {
        $this->data[$completeAction->action->id] = $completeAction;
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
    }
}
