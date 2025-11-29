<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

final readonly class Log
{
    public function __construct(
        /** @var string[] */
        public array $actionLog,

        /** @var string[] */
        public array $mainEventLog,

        /** @var string[] */
        public array $repeatedEventLog,

        /** @var string[] */
        public array $eventLog,

        /** @var string[] */
        public array $retriesLog,

        /** @var string[] */
        public array $successLog,

        /** @var string[] */
        public array $suspendedLog,

        /** @var string[] */
        public array $failLog,
        public ?string $beginAction,
        public ?string $errorAction,
    ) {}

    public function toArray(): array
    {
        return [
            'action' => $this->actionLog,
            'main_event' => $this->mainEventLog,
            'repeated_event' => $this->repeatedEventLog,
            'event' => $this->eventLog,
            'retries' => $this->retriesLog,
            'success' => $this->successLog,
            'suspended' => $this->suspendedLog,
            'fail' => $this->failLog,
            'begin_action' => $this->beginAction,
            'error_action' => $this->errorAction,
        ];
    }
}
