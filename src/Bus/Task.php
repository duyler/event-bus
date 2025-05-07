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
        $this->startFiber($actionRunner->getCallback());
    }

    public function reject(): void
    {
        $this->isRejected = true;
    }

    public function retry(): void
    {
        if (!$this->runner) {
            throw new LogicException('Runner is not initialized');
        }
        $this->startFiber($this->runner->getCallback());
    }

    public function isRunning(): bool
    {
        return $this->fiber?->isSuspended() ?? false;
    }

    public function resume(mixed $data = null): void
    {
        if ($this->fiber) {
            $this->value = $this->fiber->resume($data);
        }
    }

    public function getResult(): Result
    {
        return $this->result ??= $this->prepareResult();
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

    public function isRejected(): bool
    {
        return $this->isRejected;
    }

    public function isReady(): bool
    {
        return $this->retryTimestamp <= new DateTimeImmutable();
    }

    private function startFiber(callable $callback): void
    {
        $this->fiber = new Fiber($callback);
        $this->value = $this->fiber->start();
    }

    private function prepareResult(): Result
    {
        $resultData = $this->fiber?->getReturn();

        if ($resultData instanceof Result) {
            $this->assertResultContract($resultData);
            return $resultData;
        }

        if (null !== $resultData) {
            return Result::success($this->assertObjectContract($resultData));
        }

        if (null !== $this->action->type || null !== $this->action->typeCollection) {
            /** @var string $contract */
            $contract = $this->action->typeCollection ?? $this->action->type;
            throw new DataForContractNotReceivedException(
                $this->action->id,
                $contract,
            );
        }

        return Result::success();
    }

    private function assertResultContract(Result $result): void
    {
        if (null === $this->action->type && null !== $result->data) {
            throw new ActionReturnValueExistsException($this->action->id);
        }

        if (null !== $result->data
            && false === $result->data instanceof $this->action->type
            || null !== $this->action->typeCollection
            && false === $result->data instanceof $this->action->typeCollection
        ) {
            /** @var string $contract */
            $contract = $this->action->typeCollection ?? $this->action->type;
            $this->throwDataMustBeCompatibleWithContractException($this->action->id, $contract);
        }

        if (null !== $this->action->type && null === $result->data && ResultStatus::Success === $result->status) {
            throw new DataForContractNotReceivedException($this->action->id, $this->action->type);
        }
    }

    private function assertObjectContract(mixed $resultData): object
    {
        if (false === is_object($resultData)) {
            throw new ActionReturnValueMustBeTypeObjectException($this->action->id, $resultData);
        }

        if (null === $this->action->type) {
            throw new ActionReturnValueExistsException($this->action->id);
        }

        if (false === $resultData instanceof $this->action->type
            || null !== $this->action->typeCollection
            && false === $resultData instanceof $this->action->typeCollection
        ) {
            /** @var string $contract */
            $contract = $this->action->typeCollection ?? $this->action->type;
            $this->throwDataMustBeCompatibleWithContractException($this->action->id, $contract);
        }

        return $resultData;
    }

    private function throwDataMustBeCompatibleWithContractException(string $actionId, string $contract): never
    {
        throw new DataMustBeCompatibleWithContractException($actionId, $contract);
    }
}
