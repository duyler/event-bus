<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

final readonly class ScheduledTask
{
    public function __construct(
        /** @var callable $callback */
        private mixed $callback,
        private int $intervalMs,
        private ?int $startDelayMs = null,
    ) {}

    public function getCallback(): callable
    {
        return $this->callback;
    }

    public function getInterval(): int
    {
        return $this->intervalMs;
    }

    public function getStartDelay(): ?int
    {
        return $this->startDelayMs;
    }
}
