<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use DateTimeImmutable;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Contract\ActionRunnerInterface;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Enum\TaskStatus;
use Duyler\EventBus\Exception\ActionReturnValueExistsException;
use Duyler\EventBus\Exception\ActionReturnValueMustBeTypeObjectException;
use Duyler\EventBus\Exception\DataForContractNotReceivedException;
use Duyler\EventBus\Exception\DataMustBeCompatibleWithContractException;
use Fiber;
use LogicException;

final class Task
{
    private mixed $value = null;
    private ?Fiber $fiber = null;
    private TaskStatus $status = TaskStatus::Primary;
    private ?ActionRunnerInterface $runner = null;
    private ?Result $result = null;
    private bool $isRejected = false;
    private DateTimeImmutable $retryTimestamp;
    private string $taskId;

    public function __construct(
        public readonly Action $action,
    ) {
        $this->taskId = spl_object_hash($this);
        $this->retryTimestamp = new DateTimeImmutable();
    }

    public function run(ActionRunnerInterface $actionRunner): void
    {
        $this->runner = $actionRunner;
        $this->fiber = new Fiber($actionRunner->getCallback());
        $this->value = $this->fiber->start();
    }

    public function isRejected(): bool
    {
        return $this->isRejected;
    }

    public function reject(): void
    {
        $this->isRejected = true;
    }

    public function retry(): void
    {
        $this->fiber = new Fiber($this->runner?->getCallback() ?? throw new LogicException('Runner is not initialized'));
        $this->value = $this->fiber->start();
    }

    public function isRunning(): bool
    {
        return $this->fiber && $this->fiber->isSuspended();
    }

    public function resume(mixed $data = null): void
    {
        $this->value = $this->fiber?->resume($data);
    }

    public function getResult(): Result
    {
        return $this->result ?? $this->result = $this->prepareResult();
    }

    // TODO Refactor
    public function prepareResult(): Result
    {
        $resultData = $this->fiber?->getReturn();

        if ($resultData instanceof Result) {
            if (null === $this->action->type && null !== $resultData->data) {
                throw new ActionReturnValueExistsException($this->action->id);
            }

            if (null !== $resultData->data && false === $resultData->data instanceof $this->action->type) {
                throw new DataMustBeCompatibleWithContractException($this->action->id, $this->action->type);
            }

            if (null !== $this->action->type && null === $resultData->data) {
                if (ResultStatus::Success === $resultData->status) {
                    throw new DataForContractNotReceivedException($this->action->id, $this->action->type);
                }
            }

            return $resultData;
        }

        if (null !== $resultData) {
            if (false === is_object($resultData)) {
                throw new ActionReturnValueMustBeTypeObjectException($this->action->id, $resultData);
            }

            if (null === $this->action->type) {
                throw new ActionReturnValueExistsException($this->action->id);
            }

            if (false === $resultData instanceof $this->action->type) {
                throw new DataMustBeCompatibleWithContractException($this->action->id, $this->action->type);
            }

            return Result::success($resultData);
        }

        if (null !== $this->action->type) {
            throw new DataForContractNotReceivedException($this->action->id, $this->action->type);
        }

        return Result::success();
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function setStatus(TaskStatus $status): void
    {
        $this->status = $status;
    }

    public function setRetryTimestamp(DateTimeImmutable $retryTimestamp): void
    {
        $this->retryTimestamp = $retryTimestamp;
    }

    public function getId(): string
    {
        return $this->taskId;
    }

    public function getRunner(): ?ActionRunnerInterface
    {
        return $this->runner;
    }

    public function isReady(): bool
    {
        return $this->retryTimestamp->format('Y-m-d H:i:s:u') <= (new DateTimeImmutable())->format('Y-m-d H:i:s:u');
    }
}
