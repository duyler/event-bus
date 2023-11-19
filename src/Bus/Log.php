<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

class Log
{
    private array $actionLog = [];
    private array $mainEventLog = [];
    private array $repeatedEventLog = [];

    public function pushActionLog(string $actionId): void
    {
        $this->actionLog[] = $actionId;
    }

    public function getActionLog(): array
    {
        return $this->actionLog;
    }

    public function pushMainEventLog(string $actionIdWithStatus): void
    {
        $this->mainEventLog[] = $actionIdWithStatus;
    }

    public function getMainEventLog(): array
    {
        return $this->mainEventLog;
    }

    public function pushRepeatedEventLog(string $actionIdWithStatus): void
    {
        $this->repeatedEventLog[] = $actionIdWithStatus;
    }

    public function getRepeatedEventLog(): array
    {
        return $this->repeatedEventLog;
    }
}
