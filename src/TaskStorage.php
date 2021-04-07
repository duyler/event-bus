<?php

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Dto\Task;

use function array_key_exists;

class TaskStorage
{
    private array $data = [];

    public function save(Task $task): void
    {
        $this->data[$task->serviceId . '.' . $task->action] = $task;
    }

    public function getAll(): array
    {
        return $this->data;
    }

    public function isExists(Task $task): bool
    {
        return array_key_exists($task->serviceId . '.' . $task->action, $this->data);
    }

    public function isExistsByActionFullName(string $actionFullName): bool
    {
        return array_key_exists($actionFullName, $this->data);
    }
}
