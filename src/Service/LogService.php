<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Bus\State;

use function current;
use function end;

readonly class LogService
{
    public function __construct(
        private State $state,
    ) {}

    public function getFirstActor(): ?string
    {
        $actorLog = $this->state->getActorLog();

        if (empty($actorLog)) {
            return null;
        }

        return (string) current($actorLog);
    }

    public function getLastActor(): ?string
    {
        $actorLog = $this->state->getActorLog();

        if (empty($actorLog)) {
            return null;
        }

        return (string) end($actorLog);
    }

    public function flushSuccessLog(): void
    {
        $this->state->flushSuccessLog();
    }
}
