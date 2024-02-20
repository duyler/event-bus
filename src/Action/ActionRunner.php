<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Action\Exception\ActionHandlerMustBeCallableException;
use Duyler\EventBus\Action\Exception\ActionReturnValueExistsException;
use Duyler\EventBus\Action\Exception\ActionReturnValueNotExistsException;
use Duyler\EventBus\Action\Exception\ActionReturnValueMustBeCompatibleException;
use Duyler\EventBus\Action\Exception\ActionReturnValueMustBeTypeObjectException;
use Duyler\EventBus\Contract\ActionRunnerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Internal\Event\ActionAfterRunEvent;
use Duyler\EventBus\Internal\Event\ActionBeforeRunEvent;
use Duyler\EventBus\Internal\Event\ActionThrownExceptionEvent;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class ActionRunner implements ActionRunnerInterface
{
    public function __construct(
        private object $actionHandler,
        private object|null $argument,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    #[Override]
    public function run(Action $action): Result
    {
        try {
            $this->eventDispatcher->dispatch(new ActionBeforeRunEvent($action));
            if (!is_callable($this->actionHandler)) {
                throw new ActionHandlerMustBeCallableException();
            }
            $resultData = ($this->actionHandler)($this->argument);
            $result = $this->prepareResult($action, $resultData);
            $this->eventDispatcher->dispatch(new ActionAfterRunEvent($action));
            return $result;
        } catch (Throwable $exception) {
            $this->eventDispatcher->dispatch(new ActionThrownExceptionEvent($action, $exception));
            throw $exception;
        }
    }

    /**
     * @throws ActionReturnValueExistsException
     * @throws ActionReturnValueNotExistsException
     * @throws ActionReturnValueMustBeCompatibleException
     */
    private function prepareResult(Action $action, mixed $resultData): Result
    {
        if ($resultData instanceof Result) {
            if ($action->contract === null && $resultData->data !== null) {
                throw new ActionReturnValueExistsException($action->id);
            }

            if ($resultData->data !== null && $resultData->data instanceof $action->contract === false) {
                throw new ActionReturnValueMustBeCompatibleException($action->id, $action->contract);
            }

            if ($action->contract !== null && $resultData->data === null) {
                throw new ActionReturnValueNotExistsException($action->id);
            }

            return $resultData;
        }

        if ($resultData !== null) {
            if (is_object($resultData) === false) {
                throw new ActionReturnValueMustBeTypeObjectException($action->id, $resultData);
            }

            if ($action->contract === null) {
                throw new ActionReturnValueExistsException($action->id);
            }

            if ($resultData instanceof $action->contract === false) {
                throw new ActionReturnValueMustBeCompatibleException($action->id, $action->contract);
            }

            return new Result(ResultStatus::Success, $resultData);
        }

        if ($action->contract !== null) {
            throw new ActionReturnValueNotExistsException($action->id);
        }

        return new Result(ResultStatus::Success);
    }
}
