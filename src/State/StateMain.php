<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Bus\Task;
use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\LogService;
use Duyler\EventBus\Service\QueueService;
use Duyler\EventBus\Service\ResultService;
use Duyler\EventBus\Service\RollbackService;
use Duyler\EventBus\Service\SubscriptionService;
use Duyler\EventBus\Service\TriggerService;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\Service\StateMainBeforeService;
use Duyler\EventBus\State\Service\StateMainBeginService;
use Duyler\EventBus\State\Service\StateMainCyclicService;
use Duyler\EventBus\State\Service\StateMainEndService;
use Duyler\EventBus\State\Service\StateMainResumeService;
use Duyler\EventBus\State\Service\StateMainSuspendService;
use Override;

readonly class StateMain implements StateMainInterface
{
    public function __construct(
        private StateHandlerStorage $stateHandlerStorage,
        private ActionContainerCollection $actionContainerCollection,
        private ActionService $actionService,
        private LogService $logService,
        private ResultService $resultService,
        private RollbackService $rollbackService,
        private SubscriptionService $subscriptionService,
        private StateSuspendContext $context,
        private TriggerService $triggerService,
        private StateContextScope $contextScope,
        private QueueService $queueService,
    ) {}

    #[Override]
    public function begin(): void
    {
        $stateService = new StateMainBeginService(
            $this->actionService,
            $this->subscriptionService,
            $this->triggerService,
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
            $this->triggerService,
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
            if (empty($handler->observed($context)) || in_array($task->action->id, $handler->observed($context))) {
                $handler->handle($stateService, $context);
            }
        }
    }

    #[Override]
    public function suspend(Task $task): void
    {
        $handlers = $this->stateHandlerStorage->getMainSuspend();

        if (empty($handlers)) {
            $value = $task->getValue();
            $result = is_callable($value) ? $value() : $value;
            $this->context->addResumeValue($task->action->id, $result);

            return;
        }

        $stateService = new StateMainSuspendService(
            $this->resultService,
            $task,
            $this->actionContainerCollection->get($task->action->id),
            $this->actionService,
            $this->triggerService,
            $this->subscriptionService,
        );

        // @todo: refactor
        $resumeValue = null;
        foreach ($handlers as $handler) {
            $context = $this->contextScope->getContext($handler::class);
            if ($handler->isResumable($stateService->getValue()) && null === $resumeValue) {
                $resumeValue = $handler->handle($stateService, $context);
                $this->context->addResumeValue($task->action->id, $resumeValue);
            } else {
                $handler->handle($stateService, $context);
            }
        }
    }

    #[Override]
    public function resume(Task $task): void
    {
        $handlers = $this->stateHandlerStorage->getMainResume();

        $resumeValue = $this->context->getResumeValue($task->action->id);

        $stateService = new StateMainResumeService(
            $task,
            $resumeValue,
            $this->resultService,
        );

        foreach ($handlers as $handler) {
            $handler->handle($stateService, $this->contextScope->getContext($handler::class));
        }

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
            $this->triggerService,
            $this->rollbackService,
            $this->subscriptionService,
        );

        foreach ($this->stateHandlerStorage->getMainAfter() as $handler) {
            $context = $this->contextScope->getContext($handler::class);
            if (empty($handler->observed($context)) || in_array($task->action->id, $handler->observed($context))) {
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
}
