<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Bus\Task;
use Duyler\EventBus\State\Service\StateMainEmptyService;
use Duyler\EventBus\Storage\ActionContainerStorage;
use Duyler\EventBus\Contract\State\StateHandlerObservedInterface;
use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\LogService;
use Duyler\EventBus\Service\QueueService;
use Duyler\EventBus\Service\ResultService;
use Duyler\EventBus\Service\RollbackService;
use Duyler\EventBus\Service\TriggerService;
use Duyler\EventBus\Service\EventService;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\Service\StateMainBeforeService;
use Duyler\EventBus\State\Service\StateMainBeginService;
use Duyler\EventBus\State\Service\StateMainCyclicService;
use Duyler\EventBus\State\Service\StateMainEndService;
use Duyler\EventBus\State\Service\StateMainResumeService;
use Duyler\EventBus\State\Service\StateMainSuspendService;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface;
use UnitEnum;

readonly class StateMain implements StateMainInterface
{
    public function __construct(
        private StateHandlerStorage $stateHandlerStorage,
        private ActionContainerStorage $actionContainerStorage,
        private ActionService $actionService,
        private LogService $logService,
        private ResultService $resultService,
        private RollbackService $rollbackService,
        private TriggerService $triggerService,
        private StateSuspendContext $context,
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
            $this->triggerService,
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

        $suspend = new Suspend(IdFormatter::reverse($task->action->id), $task->getValue());

        $stateService = new StateMainSuspendService(
            $suspend,
            $this->resultService,
            $this->actionContainerStorage->get($task->action->id),
            $this->actionService,
            $this->eventService,
            $this->triggerService,
        );

        $this->context->addSuspend($task->action->id, $suspend);

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

        $suspend = $this->context->getSuspend($task->action->id);

        $stateService = new StateMainResumeService(
            $suspend,
            $this->resultService,
            $this->actionContainerStorage->get($task->action->id),
            $this->actionService,
            $this->eventService,
            $this->triggerService,
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

        $resumeValue = is_callable($suspend->value) ? ($suspend->value)() : $suspend->value;
        $task->resume($resumeValue);
    }

    #[Override]
    public function after(Task $task): void
    {
        $stateService = new StateMainAfterService(
            $task->getResult()->status,
            $task->getResult()->data,
            $task->action->id,
            $this->actionService,
            $this->resultService,
            $this->logService,
            $this->eventService,
            $this->rollbackService,
            $this->triggerService,
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
            $this->triggerService,
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

        $this->contextScope->cleanUp();
    }

    private function isObserved(StateHandlerObservedInterface $handler, Task $task, StateContext $context): bool
    {
        $observed = $handler->observed($context);
        /** @var string|UnitEnum $actionId */
        foreach ($observed as $actionId) {
            $observed[] = IdFormatter::toString($actionId);
        }
        return count($observed) === 0 || in_array($task->action->id, $observed);
    }
}
