<?php

declare(strict_types=1);

namespace Konveyer\EventBus\Storage;

use Konveyer\EventBus\DTO\Result;
use Konveyer\EventBus\ActionIdBuilder;
use Konveyer\EventBus\Task;

use function array_key_exists;
use function array_intersect_key;
use function array_flip;

class TaskStorage
{
    private array $tasks = [];

    public function save(Task $task): void
    {
        $this->tasks[ActionIdBuilder::byAction($task->action)] = $task;
    }

    public function getAll(): array
    {
        return $this->tasks;
    }

    public function isExists(string $actionFullName): bool
    {
        return array_key_exists($actionFullName, $this->tasks);
    }

    public function getByActionFullName(string $actionFullName): Task
    {
        return $this->tasks[$actionFullName];
    }

    public function getAllByRequested(array $required): array
    {
        return array_intersect_key($this->tasks, array_flip($required));
    }

    public function getResult(string $actionFullName): Result
    {
        return $this->tasks[$actionFullName]->result;
    }
}
