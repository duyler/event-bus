<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Service;

use Duyler\ActionBus\Bus\Log;

readonly class LogService
{
    public function __construct(private Log $log) {}

    public function getFirstAction(): ?string
    {
        $actionLog = $this->log->getActionLog();

        if (empty($actionLog)) {
            return null;
        }

        return (string) current($actionLog);
    }

    public function getLastAction(): ?string
    {
        $actionLog = $this->log->getActionLog();

        if (empty($actionLog)) {
            return null;
        }

        return (string) end($actionLog);
    }
}
