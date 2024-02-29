<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Service\QueueService;

/**
 * @property QueueService $queueService
 */
trait QueueServiceTrait
{
    public function queueIsEmpty(): bool
    {
        return $this->queueService->isEmpty();
    }

    public function queueIsNotEmpty(): bool
    {
        return $this->queueService->isNotEmpty();
    }

    public function inQueue(string $actionId): bool
    {
        return $this->queueService->inQueue($actionId);
    }
}
