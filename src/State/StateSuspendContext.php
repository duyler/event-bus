<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State;

class StateSuspendContext
{
    /** @var array<string, Suspend[]> */
    private array $suspend = [];

    public function addSuspend(string $actionId, Suspend $suspend): void
    {
        $this->suspend[$actionId][] = $suspend;
    }

    public function getSuspend(string $actionId): Suspend
    {
        return array_shift($this->suspend[$actionId]);
    }
}
