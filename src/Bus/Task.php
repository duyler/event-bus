<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Closure;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Exception\ActionReturnValueExistsException;
use Duyler\EventBus\Exception\ActionReturnValueMustBeTypeObjectException;
use Duyler\EventBus\Exception\DataForContractNotReceivedException;
use Duyler\EventBus\Exception\DataMustBeCompatibleWithContractException;
use Fiber;

final class Task
{
    public readonly Action $action;
    private mixed $value = null;
    private ?Fiber $fiber = null;

    public function __construct(Action $action)
    {
        $this->action = $action;
    }

    public function run(Closure $actionHandler): void
    {
        $this->fiber = new Fiber($actionHandler);
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

    // TODO Refactor
    public function getResult(): Result
    {
        $resultData = $this->fiber?->getReturn();

        if ($resultData instanceof Result) {
            if (null === $this->action->contract && null !== $resultData->data) {
                throw new ActionReturnValueExistsException($this->action->id);
            }

            if (null !== $resultData->data && false === $resultData->data instanceof $this->action->contract) {
                throw new DataMustBeCompatibleWithContractException($this->action->id, $this->action->contract);
            }

            if (null !== $this->action->contract && null === $resultData->data) {
                if (ResultStatus::Success === $resultData->status) {
                    throw new DataForContractNotReceivedException($this->action->id, $this->action->contract);
                }
            }

            return $resultData;
        }

        if (null !== $resultData) {
            if (false === is_object($resultData)) {
                throw new ActionReturnValueMustBeTypeObjectException($this->action->id, $resultData);
            }

            if (null === $this->action->contract) {
                throw new ActionReturnValueExistsException($this->action->id);
            }

            if (false === $resultData instanceof $this->action->contract) {
                throw new DataMustBeCompatibleWithContractException($this->action->id, $this->action->contract);
            }

            return new Result(ResultStatus::Success, $resultData);
        }

        if (null !== $this->action->contract) {
            throw new DataForContractNotReceivedException($this->action->id, $this->action->contract);
        }

        return new Result(ResultStatus::Success);
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
