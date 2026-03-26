<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Bus\Task;
use Duyler\EventBus\Contract\State\StateHandlerObservedInterface;
use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\EventService;
use Duyler\EventBus\Service\LogService;
use Duyler\EventBus\Service\QueueService;
use Duyler\EventBus\Service\ResultService;
use Duyler\EventBus\Service\RollbackService;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\Service\StateMainBeforeService;
use Duyler\EventBus\State\Service\StateMainBeginService;
use Duyler\EventBus\State\Service\StateMainCyclicService;
use Duyler\EventBus\State\Service\StateMainEmptyService;
use Duyler\EventBus\State\Service\StateMainEndService;
use Duyler\EventBus\State\Service\StateMainResumeService;
use Duyler\EventBus\State\Service\StateMainSuspendService;
use Duyler\EventBus\State\Service\StateMainUnresolvedService;
use Duyler\EventBus\Storage\ActionContainerStorage;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface;

use function in_array;
use function is_callable;

readonly class StateMain implements StateMainInterface
{
    public function __construct(
        private StateHandlerStorage $stateHandlerStorage,
        private ActionContainerStorage $actionContainerStorage,
        private ActionService $actionService,
        private LogService $logService,
        private ResultService $resultService,
        private RollbackService $rollbackService,
        private StateSuspendContext $suspendContext,
        private EventService $eventService,
        private StateContextScope $contextScope,
        private QueueService $queueService,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    #[Override]
    public function begin(): void
    {
        $stateService = new StateMainBeginService(
            $this->actionService,
            $this->eventService,
        );

        foreach ($this->stateHandlerStorage->getMainBegin() as $handler) {
            $handler->handle($stateService, $this->contextScope->getContext($handler::class));
        }
    }

    #[Override]
    public function cyclic(): void
    {
        $stateService = new StateMainCyclicService(
            $this->queueService,
            $this->actionService,
            $this->eventService,
            $this->resultService,
            $this->eventDispatcher,
        );

        foreach ($this->stateHandlerStorage->getMainCyclic() as $handler) {
            $handler->handle($stateService, $this->contextScope->getContext($handler::class));
        }
    }

    #[Override]
    public function before(Task $task): void
    {
        $stateService = new StateMainBeforeService(
            $task,
            $this->logService,
            $this->actionService,
            $this->queueService,
        );

        foreach ($this->stateHandlerStorage->getMainBefore() as $handler) {
            $context = $this->contextScope->getContext($handler::class);
            if ($this->isObserved($handler, $task, $context)) {
                $handler->handle($stateService, $context);
            }
        }
    }

    #[Override]
    public function suspend(Task $task): void
    {
        $handlers = $this->stateHandlerStorage->getMainSuspend();

        $suspend = new Suspend($task->action->getExternalId(), $task->getValue());

        $stateService = new StateMainSuspendService(
            $suspend,
            $this->resultService,
            $this->actionContainerStorage->get($task->action->getId()),
            $this->actionService,
            $this->eventService,
        );

        $this->suspendContext->addSuspend($task->action->getId(), $suspend);

        foreach ($handlers as $handler) {
            $context = $this->contextScope->getContext($handler::class);
            if ($handler->observed($suspend, $context)) {
                $handler->handle($stateService, $context);
            }
        }
    }

    #[Override]
    public function resume(Task $task): void
    {
        $handlers = $this->stateHandlerStorage->getMainResume();

        $suspend = $this->suspendContext->getSuspend($task->action->getId());

        $stateService = new StateMainResumeService(
            $suspend,
            $this->resultService,
            $this->actionContainerStorage->get($task->action->getId()),
            $this->actionService,
            $this->eventService,
        );

        foreach ($handlers as $handler) {
            $context = $this->contextScope->getContext($handler::class);
            if ($handler->observed($suspend, $context)) {
                $handler->handle($stateService, $context);
            }
        }

        if ($suspend->resumeValueIsExists()) {
            $task->resume($suspend->getResumeValue());
            return;
        }

        if (null === $suspend->value) {
            $task->resume();
            return;
        }

        if (is_callable($suspend->value)) {
            $task->resume(($suspend->value)($task->getScope()));
        } else {
            $task->resume();
        }
    }

    #[Override]
    public function after(Task $task): void
    {
        $stateService = new StateMainAfterService(
            $task->getResult()->status,
            $task->getResult()->data,
            $task->action->getExternalId(),
            $task->getScope(),
            $this->actionService,
            $this->resultService,
            $this->logService,
            $this->eventService,
            $this->rollbackService,
        );

        foreach ($this->stateHandlerStorage->getMainAfter() as $handler) {
            $context = $this->contextScope->getContext($handler::class);
            if ($this->isObserved($handler, $task, $context)) {
                $handler->handle($stateService, $context);
            }
        }
    }

    #[Override]
    public function empty(): void
    {
        $stateService = new StateMainEmptyService(
            $this->actionService,
            $this->resultService,
            $this->logService,
            $this->eventService,
            $this->rollbackService,
            $this->eventDispatcher,
        );

        foreach ($this->stateHandlerStorage->getMainEmpty() as $handler) {
            $handler->handle($stateService, $this->contextScope->getContext($handler::class));
        }
    }

    #[Override]
    public function end(): void
    {
        $stateService = new StateMainEndService(
            $this->resultService,
            $this->logService,
            $this->rollbackService,
            $this->eventDispatcher,
        );

        foreach ($this->stateHandlerStorage->getMainEnd() as $handler) {
            $handler->handle($stateService, $this->contextScope->getContext($handler::class));
        }

        $this->contextScope->finalize();
    }

    #[Override]
    public function unresolved(Task $task): void
    {
        $stateService = new StateMainUnresolvedService(
            $this->resultService,
            $this->logService,
            $this->rollbackService,
            $this->actionService,
            $this->queueService,
            $task,
        );

        foreach ($this->stateHandlerStorage->getMainUnresolved() as $handler) {
            $context = $this->contextScope->getContext($handler::class);
            if ($this->isObserved($handler, $task, $context)) {
                $handler->handle($stateService, $context);
            }
        }
    }

    private function isObserved(StateHandlerObservedInterface $handler, Task $task, StateContext $context): bool
    {
        $observed = $handler->observed($context);
        return count($observed) === 0 || in_array($task->action->getExternalId(), $observed);
    }
}
