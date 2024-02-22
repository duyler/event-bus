<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Log as LogDto;

class Log
{
    /** @var string[] */
    private array $actionLog = [];

    /** @var string[] */
    private array $mainEventLog = [];

    /** @var string[] */
    private array $repeatedEventLog = [];

    /** @var string[] */
    private array $triggerLog = [];

    public function pushActionLog(Action $action): void
    {
        $this->actionLog[] = $action->id;
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

    public function pushTriggerEventLog(string $triggerId): void
    {
        $this->triggerLog[] = $triggerId;
    }

    public function getTriggerEventLog(): array
    {
        return $this->triggerLog;
    }

    public function getLog(): LogDto
    {
        return new LogDto(
            $this->actionLog,
            $this->mainEventLog,
            $this->repeatedEventLog,
            $this->triggerLog
        );
    }

    public function reset(): void
    {
        $this->actionLog = [];
        $this->mainEventLog = [];
        $this->repeatedEventLog = [];
    }
}
