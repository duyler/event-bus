<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State;

use Duyler\ActionBus\Bus\Task;
use Duyler\ActionBus\Storage\ActionContainerStorage;
use Duyler\ActionBus\Contract\State\StateHandlerObservedInterface;
use Duyler\ActionBus\Contract\StateMainInterface;
use Duyler\ActionBus\Formatter\IdFormatter;
use Duyler\ActionBus\Service\ActionService;
use Duyler\ActionBus\Service\LogService;
use Duyler\ActionBus\Service\QueueService;
use Duyler\ActionBus\Service\ResultService;
use Duyler\ActionBus\Service\RollbackService;
use Duyler\ActionBus\Service\SubscriptionService;
use Duyler\ActionBus\Service\EventService;
use Duyler\ActionBus\State\Service\StateMainAfterService;
use Duyler\ActionBus\State\Service\StateMainBeforeService;
use Duyler\ActionBus\State\Service\StateMainBeginService;
use Duyler\ActionBus\State\Service\StateMainCyclicService;
use Duyler\ActionBus\State\Service\StateMainEndService;
use Duyler\ActionBus\State\Service\StateMainResumeService;
use Duyler\ActionBus\State\Service\StateMainSuspendService;
use Override;
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
        private SubscriptionService $subscriptionService,
        private StateSuspendContext $context,
        private EventService $eventService,
        private StateContextScope $contextScope,
        private QueueService $queueService,
    ) {}

    #[Override]
    public function begin(): void
    {
        $stateService = new StateMainBeginService(
            $this->actionService,
            $this->subscriptionService,
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
        );

        foreach ($this->stateHandlerStorage->getMainCyclic() as $handler) {
            $handler->handle($stateService, $this->contextScope->getContext($handler::class));
        }
    }

    #[Override]
    public function before(Task $task): void
    {
        $stateService = new StateMainBeforeService(
            $task->action->id,
            $this->logService,
            $this->actionService,
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
            $this->subscriptionService,
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
            $this->subscriptionService,
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
            $this->subscriptionService,
        );

        foreach ($this->stateHandlerStorage->getMainAfter() as $handler) {
            $context = $this->contextScope->getContext($handler::class);
            if ($this->isObserved($handler, $task, $context)) {
                $handler->handle($stateService, $context);
            }
        }
    }

    #[Override]
    public function end(): void
    {
        $stateService = new StateMainEndService(
            $this->resultService,
            $this->logService,
            $this->rollbackService,
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
