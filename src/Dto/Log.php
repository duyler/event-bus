<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

readonly class Log
{
    public function __construct(
        /** @var string[] */
        private array $actionLog,

        /** @var string[] */
        private array $mainEventLog,

        /** @var string[] */
        private array $repeatedEventLog,

        /** @var string[] */
        private array $triggerLog,
    ) {}

    public function getActionLog(): array
    {
        return $this->actionLog;
    }

    public function getMainEventLog(): array
    {
        return $this->mainEventLog;
    }

    public function getRepeatedEventLog(): array
    {
        return $this->repeatedEventLog;
    }

    public function getTriggerEventLog(): array
    {
        return $this->triggerLog;
    }
}
