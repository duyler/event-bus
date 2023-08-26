<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use RuntimeException;
use SplQueue;

class TaskQueue
{
    private SplQueue $queue;
    private array $queueLog = [];

    public function __construct(SplQueue $splQueue)
    {
        $this->queue = $splQueue;
        $this->queue->setIteratorMode(SplQueue::IT_MODE_DELETE);
    }

    public function push(Task $task): void
    {
        $this->queue->push($task);
        $this->queueLog[] = $task->action->id;
    }

    public function isNotEmpty(): bool
    {
        return $this->queue->isEmpty() === false;
    }

    public function isEmpty(): bool
    {
        return $this->queue->isEmpty();
    }

    public function dequeue(): Task
    {
        if ($this->queue->isEmpty()) {
            throw new RuntimeException("TaskQueue is empty");
        }

        /** @var Task $task */
        $task = $this->queue->dequeue();

        unset($this->queueLog[$task->action->id]);

        return $task;
    }

    public function inQueue(string $actionId): bool
    {
        return in_array($actionId, $this->queueLog);
    }
}
