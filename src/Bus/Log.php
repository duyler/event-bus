<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\DependencyInjection\Attribute\Reset;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Log as LogDto;

#[Reset]
final class Log
{
    /** @var string[] */
    private array $actionLog = [];

    /** @var string[] */
    private array $mainLog = [];

    /** @var string[] */
    private array $repeatedLog = [];

    /** @var string[] */
    private array $triggerLog = [];

    /** @var string[] */
    private array $retriesLog = [];

    public function __construct(private BusConfig $config) {}

    public function pushCompleteAction(CompleteAction $completeAction): void
    {
        $actionId = $completeAction->action->id . '.' . $completeAction->result->status->value;

        if (in_array($actionId, $this->mainLog) && 0 === $completeAction->action->retries) {
            $this->pushRepeatedLog($actionId);
            $this->pushRetriesLog($actionId);
        } else {
            $this->pushMainLog($actionId);
        }

        $this->pushActionLog($completeAction->action);
    }

    private function pushActionLog(Action $action): void
    {
        if ($this->config->allowCircularCall && count($this->actionLog) === $this->config->logMaxSize) {
            array_shift($this->actionLog);
        }
        $this->actionLog[] = $action->id;
    }

    public function getActionLog(): array
    {
        return $this->actionLog;
    }

    private function pushMainLog(string $actionIdWithStatus): void
    {
        if ($this->config->allowCircularCall && count($this->mainLog) === $this->config->logMaxSize) {
            array_shift($this->mainLog);
        }
        $this->mainLog[] = $actionIdWithStatus;
    }

    public function getMainLog(): array
    {
        return $this->mainLog;
    }

    private function pushRepeatedLog(string $actionIdWithStatus): void
    {
        if ($this->config->allowCircularCall && count($this->repeatedLog) === $this->config->logMaxSize) {
            array_shift($this->repeatedLog);
        }
        $this->repeatedLog[] = $actionIdWithStatus;
    }

    private function pushRetriesLog(string $actionIdWithStatus): void
    {
        if ($this->config->allowCircularCall && count($this->retriesLog) === $this->config->logMaxSize) {
            array_shift($this->retriesLog);
        }
        $this->retriesLog[] = $actionIdWithStatus;
    }

    public function getRepeatedLog(): array
    {
        return $this->repeatedLog;
    }

    public function pushTriggerLog(string $triggerId): void
    {
        if ($this->config->allowCircularCall && count($this->triggerLog) === $this->config->logMaxSize) {
            array_shift($this->triggerLog);
        }
        $this->triggerLog[] = $triggerId;
    }

    public function getLog(): LogDto
    {
        return new LogDto(
            $this->actionLog,
            $this->mainLog,
            $this->repeatedLog,
            $this->triggerLog,
            $this->retriesLog,
        );
    }

    public function reset(): void
    {
        $this->actionLog = [];
        $this->mainLog = [];
        $this->repeatedLog = [];
        $this->triggerLog = [];
        $this->retriesLog = [];
    }
}
