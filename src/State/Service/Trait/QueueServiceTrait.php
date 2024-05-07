<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service\Trait;

use Duyler\ActionBus\Formatter\ActionIdFormatter;
use Duyler\ActionBus\Service\QueueService;
use UnitEnum;

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

    public function inQueue(string|UnitEnum $actionId): bool
    {
        return $this->queueService->inQueue(ActionIdFormatter::toString($actionId));
    }

    public function queueCount(): int
    {
        return $this->queueService->count();
    }
}
