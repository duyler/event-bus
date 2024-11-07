<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Log as LogDto;
use Duyler\DI\Attribute\Finalize;
use Duyler\EventBus\Enum\Mode;
use Duyler\EventBus\Enum\ResultStatus;

#[Finalize(method: 'reset')]
final class State
{
    /** @var string[] */
    private array $actionLog = [];

    /** @var string[] */
    private array $mainLog = [];

    /** @var string[] */
    private array $repeatedLog = [];

    /** @var string[] */
    private array $eventLog = [];

    /** @var string[] */
    private array $retriesLog = [];

    /** @var string[] */
    private array $successLog = [];

    /** @var string[] */
    private array $failLog = [];

    /** @var string[] */
    private array $suspendedLog = [];

    private ?string $beginAction = null;

    private ?string $errorAction = null;

    public function __construct(private BusConfig $config) {}

    public function pushCompleteAction(CompleteAction $completeAction): void
    {
        $actionId = $completeAction->action->id . '.' . $completeAction->result->status->value;

        if (in_array($actionId, $this->mainLog) && 0 === $completeAction->action->retries) {
            $this->pushRepeatedLog($actionId);
            $this->pushRetriesLog($actionId);
        } else {
            $this->pushMainLog($actionId);
            if (ResultStatus::Success === $completeAction->result->status) {
                $this->pushSuccessLog($completeAction->action->id);
            } else {
                $this->pushFailLog($completeAction->action->id);
            }
        }

        $this->pushActionLog($completeAction->action);
    }

    private function pushActionLog(Action $action): void
    {
        if ($this->isLooped() && count($this->actionLog) === $this->config->logMaxSize) {
            array_shift($this->actionLog);
        }
        $this->actionLog[] = $action->id;
    }

    private function pushSuccessLog(string $actionId): void
    {
        if ($this->isLooped() && count($this->successLog) === $this->config->logMaxSize) {
            array_shift($this->successLog);
        }
        $this->successLog[] = $actionId;
    }

    public function pushSuspendedLog(string $actionId): void
    {
        if ($this->isLooped() && count($this->actionLog) === $this->config->logMaxSize) {
            array_shift($this->suspendedLog);
        }
        $this->suspendedLog[] = $actionId;
    }

    public function resolveResumeAction(string $actionId): void
    {
        if (in_array($actionId, $this->suspendedLog)) {
            unset($this->suspendedLog[array_search($actionId, $this->suspendedLog)]);
        }
    }

    private function pushFailLog(string $actionId): void
    {
        if ($this->isLooped() && count($this->successLog) === $this->config->logMaxSize) {
            array_shift($this->failLog);
        }
        $this->failLog[] = $actionId;
    }

    public function getActionLog(): array
    {
        return $this->actionLog;
    }

    private function pushMainLog(string $actionIdWithStatus): void
    {
        if ($this->isLooped() && count($this->mainLog) === $this->config->logMaxSize) {
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
        if ($this->isLooped() && count($this->repeatedLog) === $this->config->logMaxSize) {
            array_shift($this->repeatedLog);
        }
        $this->repeatedLog[] = $actionIdWithStatus;
    }

    private function pushRetriesLog(string $actionIdWithStatus): void
    {
        if ($this->isLooped() && count($this->retriesLog) === $this->config->logMaxSize) {
            array_shift($this->retriesLog);
        }
        $this->retriesLog[] = $actionIdWithStatus;
    }

    public function getRepeatedLog(): array
    {
        return $this->repeatedLog;
    }

    public function pushEventLog(string $eventId): void
    {
        if ($this->isLooped() && count($this->eventLog) === $this->config->logMaxSize) {
            array_shift($this->eventLog);
        }
        $this->eventLog[] = $eventId;
    }

    /** @return string[] */
    public function getSuccessLog(): array
    {
        return $this->successLog;
    }

    public function flushSuccessLog(): void
    {
        $this->successLog = [];
    }

    public function getLog(): LogDto
    {
        return new LogDto(
            $this->actionLog,
            $this->mainLog,
            $this->repeatedLog,
            $this->eventLog,
            $this->retriesLog,
            $this->successLog,
            $this->failLog,
            $this->suspendedLog,
            $this->beginAction,
            $this->errorAction,
        );
    }

    private function isLooped(): bool
    {
        return Mode::Loop === $this->config->mode || $this->config->allowCircularCall;
    }

    public function setBeginAction(string $actionId): void
    {
        $this->beginAction = $actionId;
    }

    public function setErrorAction(string $actionId): void
    {
        $this->errorAction = $actionId;
    }

    public function reset(): void
    {
        $this->actionLog = [];
        $this->mainLog = [];
        $this->repeatedLog = [];
        $this->eventLog = [];
        $this->retriesLog = [];
        $this->successLog = [];
        $this->failLog = [];
        $this->suspendedLog = [];
        $this->beginAction = null;
        $this->errorAction = null;
    }
}
