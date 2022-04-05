<?php

declare(strict_types=1);

namespace Konveyer\EventBus;

use RuntimeException;
use SplQueue;

class TaskQueue
{
    private SplQueue $runningQueue;
    private SplQueue $notRunningQueue;

    public function __construct()
    {
        $this->runningQueue = new SplQueue();
        $this->runningQueue->setIteratorMode(SplQueue::IT_MODE_DELETE);
        $this->notRunningQueue = new SplQueue();
        $this->notRunningQueue->setIteratorMode(SplQueue::IT_MODE_DELETE);
    }

    public function add(Task $task): void
    {
        if ($task->isRunning()) {
            $this->runningQueue->push($task);
        } else {
            $this->runningQueue->push($task);
        }
    }

    public function isNotEmpty(): bool
    {
        return $this->runningQueue->isEmpty() === false || $this->notRunningQueue->isEmpty() === false;
    }

    public function dequeue(): mixed
    {
        if ($this->notRunningQueue->isEmpty() === false) {
            return $this->notRunningQueue->dequeue();
        }

        if ($this->runningQueue->isEmpty()) {
            throw new RuntimeException("Queue is empty");
        }

        return $this->runningQueue->dequeue();
    }
}
