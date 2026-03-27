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
        $this->tasks[$task->actor->getId()][$task->getId()] = $task;
    }

    public function get(string $actorId, string $taskId): Task
    {
        return $this->tasks[$actorId][$taskId];
    }

    /** @return array<string, Task> */
    public function getAllByActorId(string $actorId): array
    {
        return $this->tasks[$actorId] ?? [];
    }

    public function remove(string $actorId, string $taskId): void
    {
        unset($this->tasks[$actorId][$taskId]);
    }

    public function finalize(): void
    {
        $this->tasks = [];
    }
}
