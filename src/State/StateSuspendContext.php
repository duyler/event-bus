<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\DI\Attribute\Finalize;

#[Finalize]
final class StateSuspendContext
{
    /** @var array<string, Suspend[]> */
    private array $suspend = [];

    public function addSuspend(string $actorId, Suspend $suspend): void
    {
        $this->suspend[$actorId][] = $suspend;
    }

    public function getSuspend(string $actorId): Suspend
    {
        /** @var Suspend $suspend */
        $suspend = array_shift($this->suspend[$actorId]);

        return $suspend;
    }

    public function finalize(): void
    {
        $this->suspend = [];
    }
}
