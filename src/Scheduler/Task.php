<?php

declare(strict_types=1);

namespace Duyler\EventBus\Scheduler;

final readonly class Task
{
    public function __construct(
        /** @var callable */
        public mixed $callback,
        public int $interval,
        public int $nextRun,
        public int $lastRun,
    ) {}
}
