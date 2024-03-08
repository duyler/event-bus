<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use RuntimeException;
use SplQueue;

final class TaskQueue
{
    private SplQueue $queue;
    private array $queueLog = [];

    public function __construct()
    {
        $this->queue = new SplQueue();
        $this->queue->setIteratorMode(SplQueue::IT_MODE_DELETE);
    }

    public function push(Task $task): void
    {
        $this->queue->push($task);
        $this->queueLog[] = $task->action->id;
    }

    public function isNotEmpty(): bool
    {
        return false === $this->queue->isEmpty();
    }

    public function isEmpty(): bool
    {
        return $this->queue->isEmpty();
    }

    public function dequeue(): Task
    {
        if ($this->queue->isEmpty()) {
            throw new RuntimeException('TaskQueue is empty');
        }

        /** @var Task $task */
        $task = $this->queue->dequeue();

        $key = array_search($task->action->id, $this->queueLog);
        unset($this->queueLog[$key]);

        return $task;
    }

    public function inQueue(string $actionId): bool
    {
        return in_array($actionId, $this->queueLog);
    }

    public function count(): int
    {
        return $this->queue->count();
    }
}
