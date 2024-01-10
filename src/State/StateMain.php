<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Bus\Task;
use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\LogService;
use Duyler\EventBus\Service\ResultService;
use Duyler\EventBus\Service\RollbackService;
use Duyler\EventBus\Service\SubscriptionService;
use Duyler\EventBus\Service\TriggerService;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\Service\StateMainBeforeService;
use Duyler\EventBus\State\Service\StateMainFinalService;
use Duyler\EventBus\State\Service\StateMainResumeService;
use Duyler\EventBus\State\Service\StateMainStartService;
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
        private StateContext $context,
        private TriggerService $triggerService,
    ) {}

    #[Override]
    public function start(): void
    {
        $stateService = new StateMainStartService(
            $this->actionService,
            $this->subscriptionService,
            $this->triggerService,
        );

        foreach ($this->stateHandlerStorage->getMainStart() as $handler) {
            $handler->handle($stateService);
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
            if (empty($handler->observed()) || in_array($task->action->id, $handler->observed())) {
                $handler->handle($stateService);
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
        );

        // @todo: refactor
        $resumeValue = null;
        foreach ($handlers as $handler) {
            if ($handler->isResumable($stateService->getValue()) && $resumeValue === null) {
                $resumeValue = $handler->handle($stateService);
                $this->context->addResumeValue($task->action->id, $handler->handle($stateService));
            } else {
                $handler->handle($stateService);
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
        );

        foreach ($handlers as $handler) {
            $handler->handle($stateService);
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
        );

        foreach ($this->stateHandlerStorage->getMainAfter() as $handler) {
            if (empty($handler->observed()) || in_array($task->action->id, $handler->observed())) {
                $handler->handle($stateService);
            }
        }
    }

    #[Override]
    public function final(): void
    {
        $stateService = new StateMainFinalService(
            $this->resultService,
            $this->logService,
            $this->rollbackService,
        );

        foreach ($this->stateHandlerStorage->getMainFinal() as $handler) {
            $handler->handle($stateService);
        }
    }
}
