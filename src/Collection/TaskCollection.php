<?php

declare(strict_types=1);

namespace Duyler\EventBus\Collection;

use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Task;
use function array_flip;
use function array_intersect_key;

class TaskCollection extends AbstractCollection
{
    public function save(Task $task): void
    {
        $this->data[$task->action->id] = $task;
    }

    /**
     * @return Task[]
     */
    public function getAllByArray(array $array): array
    {
        return array_intersect_key($this->data, array_flip($array));
    }

    public function getResult(string $actionId): ?Result
    {
        return $this->data[$actionId]->result ?? null;
    }

    public function get(string $actionId): Task
    {
        return $this->data[$actionId];
    }

    public function isExists(string $actionId): bool
    {
        return array_key_exists($actionId, $this->data);
    }
}
