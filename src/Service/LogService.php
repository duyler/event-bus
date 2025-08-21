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

    public function getFirstAction(): ?string
    {
        $actionLog = $this->state->getActionLog();

        if (empty($actionLog)) {
            return null;
        }

        return (string) current($actionLog);
    }

    public function getLastAction(): ?string
    {
        $actionLog = $this->state->getActionLog();

        if (empty($actionLog)) {
            return null;
        }

        return (string) end($actionLog);
    }

    public function flushSuccessLog(): void
    {
        $this->state->flushSuccessLog();
    }
}
