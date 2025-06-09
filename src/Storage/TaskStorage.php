<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\DI\Attribute\Finalize;
use Duyler\EventBus\Bus\Task;

#[Finalize]
final class TaskStorage
{
    /** @var array<string, array<string, Task>> */
    private array $tasks = [];

    public function add(Task $task): void
    {
        $this->tasks[$task->action->getId()][$task->getId()] = $task;
    }

    public function get(string $actionId, string $taskId): Task
    {
        return $this->tasks[$actionId][$taskId];
    }

    /** @return array<string, Task> */
    public function getAllByActionId(string $actionId): array
    {
        return $this->tasks[$actionId] ?? [];
    }

    public function remove(string $actionId, string $taskId): void
    {
        unset($this->tasks[$actionId][$taskId]);
    }

    public function finalize(): void
    {
        $this->tasks = [];
    }
}
