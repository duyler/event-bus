<?php

declare(strict_types=1);

namespace Duyler\EventBus\Collection;

use Duyler\EventBus\Bus\CompleteAction;
use Duyler\EventBus\Dto\Result;

class CompleteActionCollection
{
    /**
     * @var array<string, CompleteAction>
     */
    private array $data = [];

    public function save(CompleteAction $event): void
    {
        $this->data[$event->action->id] = $event;
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

    public function getAll(): array
    {
        return $this->data;
    }

    public function cleanUp(): void
    {
        $this->data = [];
    }
}
