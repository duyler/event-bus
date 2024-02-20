<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Dto\Action;

class Log
{
    /** @var string[] */
    private array $actionLog = [];

    /** @var string[] */
    private array $mainEventLog = [];

    /** @var string[] */
    private array $repeatedEventLog = [];

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

    public function cleanUp(): void
    {
        $this->actionLog = [];
        $this->mainEventLog = [];
        $this->repeatedEventLog = [];
    }
}
