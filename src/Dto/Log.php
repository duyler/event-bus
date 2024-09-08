<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

final readonly class Log
{
    public function __construct(
        /** @var string[] */
        private array $actionLog,

        /** @var string[] */
        private array $mainEventLog,

        /** @var string[] */
        private array $repeatedEventLog,

        /** @var string[] */
        private array $eventLog,

        /** @var string[] */
        private array $retriesLog,
    ) {}

    public function getActionLog(): array
    {
        return $this->actionLog;
    }

    public function getMainActionLog(): array
    {
        return $this->mainEventLog;
    }

    public function getRepeatedActionLog(): array
    {
        return $this->repeatedEventLog;
    }

    public function getEventLog(): array
    {
        return $this->eventLog;
    }

    public function getRetriesLog(): array
    {
        return $this->retriesLog;
    }
}
