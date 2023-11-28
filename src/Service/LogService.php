<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Bus\Log;

readonly class LogService
{
    public function __construct(private Log $log)
    {
    }

    public function getFirstAction(): string
    {
        $actionLog = $this->log->getActionLog();

        return $actionLog[array_key_first($actionLog)];
    }

    public function getLastAction(): string
    {
        $actionLog = $this->log->getActionLog();

        return $actionLog[array_key_last($actionLog)];
    }
}
