<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\DI\Attribute\Finalize;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Log as LogDto;
use Duyler\EventBus\Enum\Mode;
use Duyler\EventBus\Enum\ResultStatus;

use function array_search;
use function array_shift;
use function count;
use function in_array;

#[Finalize(method: 'reset')]
final class State
{
    /** @var string[] */
    private array $actorLog = [];

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

    private ?string $beginActor = null;

    private ?string $errorActor = null;

    public function __construct(private readonly BusConfig $config) {}

    public function pushCompleteActor(CompleteActor $completeActor): void
    {
        $actorId = $completeActor->actor->getId() . '.' . $completeActor->result->status->value;

        if (in_array($actorId, $this->mainLog)
            && 0 === $completeActor->actor->getRetries()
            && false === $completeActor->actor->isRepeatable()
        ) {
            $this->pushRepeatedLog($actorId);
            $this->pushRetriesLog($actorId);
        } else {
            $this->pushMainLog($actorId);
            if (ResultStatus::Success === $completeActor->result->status) {
                $this->pushSuccessLog($completeActor->actor->getId());
            } else {
                $this->pushFailLog($completeActor->actor->getId());
            }
        }

        $this->pushActorLog($completeActor->actor);
    }

    private function pushActorLog(Actor $actor): void
    {
        if ($this->isLooped() && count($this->actorLog) === $this->config->logMaxSize) {
            array_shift($this->actorLog);
        }
        $this->actorLog[] = $actor->getId();
    }

    private function pushSuccessLog(string $actorId): void
    {
        if ($this->isLooped() && count($this->successLog) === $this->config->logMaxSize) {
            array_shift($this->successLog);
        }
        $this->successLog[] = $actorId;
    }

    public function pushSuspendedLog(string $actorId): void
    {
        if ($this->isLooped() && count($this->actorLog) === $this->config->logMaxSize) {
            array_shift($this->suspendedLog);
        }
        $this->suspendedLog[] = $actorId;
    }

    public function resolveResumeActor(string $actorId): void
    {
        if (in_array($actorId, $this->suspendedLog)) {
            unset($this->suspendedLog[array_search($actorId, $this->suspendedLog)]);
        }
    }

    private function pushFailLog(string $actorId): void
    {
        if ($this->isLooped() && count($this->successLog) === $this->config->logMaxSize) {
            array_shift($this->failLog);
        }
        $this->failLog[] = $actorId;
    }

    public function getActorLog(): array
    {
        return $this->actorLog;
    }

    private function pushMainLog(string $actorIdWithStatus): void
    {
        if ($this->isLooped() && count($this->mainLog) === $this->config->logMaxSize) {
            array_shift($this->mainLog);
        }
        $this->mainLog[] = $actorIdWithStatus;
    }

    public function getMainLog(): array
    {
        return $this->mainLog;
    }

    private function pushRepeatedLog(string $actorIdWithStatus): void
    {
        if ($this->isLooped() && count($this->repeatedLog) === $this->config->logMaxSize) {
            array_shift($this->repeatedLog);
        }
        $this->repeatedLog[] = $actorIdWithStatus;
    }

    private function pushRetriesLog(string $actorIdWithStatus): void
    {
        if ($this->isLooped() && count($this->retriesLog) === $this->config->logMaxSize) {
            array_shift($this->retriesLog);
        }
        $this->retriesLog[] = $actorIdWithStatus;
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
            $this->actorLog,
            $this->mainLog,
            $this->repeatedLog,
            $this->eventLog,
            $this->retriesLog,
            $this->successLog,
            $this->failLog,
            $this->suspendedLog,
            $this->beginActor,
            $this->errorActor,
        );
    }

    private function isLooped(): bool
    {
        return Mode::Loop === $this->config->mode || $this->config->allowCircularCall;
    }

    public function setBeginActor(string $actorId): void
    {
        $this->beginActor = $actorId;
    }

    public function setErrorActor(string $actorId): void
    {
        $this->errorActor = $actorId;
    }

    public function reset(): void
    {
        $this->actorLog = [];
        $this->mainLog = [];
        $this->repeatedLog = [];
        $this->eventLog = [];
        $this->retriesLog = [];
        $this->successLog = [];
        $this->failLog = [];
        $this->suspendedLog = [];
        $this->beginActor = null;
        $this->errorActor = null;
    }
}
