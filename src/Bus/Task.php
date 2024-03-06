<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Action\Exception\ActionReturnValueExistsException;
use Duyler\EventBus\Action\Exception\ActionReturnValueMustBeCompatibleException;
use Duyler\EventBus\Action\Exception\ActionReturnValueMustBeTypeObjectException;
use Duyler\EventBus\Action\Exception\ActionReturnValueNotExistsException;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Closure;
use Duyler\EventBus\Enum\ResultStatus;
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
            if ($this->action->contract === null && $resultData->data !== null) {
                throw new ActionReturnValueExistsException($this->action->id);
            }

            if ($resultData->data !== null && $resultData->data instanceof $this->action->contract === false) {
                throw new ActionReturnValueMustBeCompatibleException($this->action->id, $this->action->contract);
            }

            if ($this->action->contract !== null && $resultData->data === null) {
                if ($resultData->status === ResultStatus::Success) {
                    throw new ActionReturnValueNotExistsException($this->action->id);
                }
            }

            return $resultData;
        }

        if ($resultData !== null) {
            if (is_object($resultData) === false) {
                throw new ActionReturnValueMustBeTypeObjectException($this->action->id, $resultData);
            }

            if ($this->action->contract === null) {
                throw new ActionReturnValueExistsException($this->action->id);
            }

            if ($resultData instanceof $this->action->contract === false) {
                throw new ActionReturnValueMustBeCompatibleException($this->action->id, $this->action->contract);
            }

            return new Result(ResultStatus::Success, $resultData);
        }

        if ($this->action->contract !== null) {
            throw new ActionReturnValueNotExistsException($this->action->id);
        }

        return new Result(ResultStatus::Success);
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
