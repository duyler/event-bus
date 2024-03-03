<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Bus\TaskQueue;

class QueueService
{
    public function __construct(private TaskQueue $taskQueue) {}

    public function isEmpty(): bool
    {
        return $this->taskQueue->isEmpty();
    }

    public function isNotEmpty(): bool
    {
        return $this->taskQueue->isNotEmpty();
    }

    public function inQueue(string $actionId): bool
    {
        return $this->taskQueue->inQueue($actionId);
    }
    public function count(): int
    {
        return $this->taskQueue->count();
    }
}
