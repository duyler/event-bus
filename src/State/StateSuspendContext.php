<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\DI\Attribute\Finalize;

#[Finalize]
final class StateSuspendContext
{
    /** @var array<string, Suspend[]> */
    private array $suspend = [];

    public function addSuspend(string $actionId, Suspend $suspend): void
    {
        $this->suspend[$actionId][] = $suspend;
    }

    public function getSuspend(string $actionId): Suspend
    {
        /** @var Suspend $suspend */
        $suspend = array_shift($this->suspend[$actionId]);

        return $suspend;
    }

    public function finalize(): void
    {
        $this->suspend = [];
    }
}
