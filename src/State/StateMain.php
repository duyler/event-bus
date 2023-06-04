<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\BusService;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\Service\StateMainBeforeService;
use Duyler\EventBus\State\Service\StateMainFinalService;
use Duyler\EventBus\State\Service\StateMainStartService;
use Duyler\EventBus\State\Service\StateMainSuspendService;
use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Task;

readonly class StateMain
{
    public function __construct(
        private BusService                $busService,
        private StateHandlerStorage       $stateHandlerStorage,
        private ActionContainerCollection $actionContainerCollection,
    ) {
    }

    public function start(): void
    {
        $stateService = new StateMainStartService(
            $this->busService,
        );

        foreach ($this->stateHandlerStorage->getStateMainStart() as $handler) {
            $handler->handle($stateService);
        }
    }

    public function before(Task $task): void
    {
        $stateService = new StateMainBeforeService(
            $task->action->id,
            $this->busService,
        );

        foreach ($this->stateHandlerStorage->getStateMainBefore() as $handler) {
            if (empty($handler->observed()) || in_array($task->action->id, $handler->observed())) {
                $handler->handle($stateService);
            }
        }
    }

    public function suspend(Task $task): void
    {
        $handler = $this->stateHandlerStorage->getStateMainSuspend();

        if (empty($handler)) {
            $value = $task->getValue();
            $result = is_callable($value) ? $value() : $value;
            $task->resume($result);
            return;
        }

        $stateService = new StateMainSuspendService(
            $this->busService,
            $task,
            $this->actionContainerCollection->get($task->action->id),
        );

        $task->resume($handler->getResume($stateService));
    }

    public function after(Task $task): void
    {
        $stateService = new StateMainAfterService(
            $task->result->status,
            $task->result->data,
            $task->action->id,
            $this->busService,
        );

        foreach ($this->stateHandlerStorage->getStateMainAfter() as $handler) {
            if (empty($handler->observed()) || in_array($task->action->id, $handler->observed())) {
                $handler->handle($stateService);
            }
        }
    }

    public function final(): void
    {
        $stateService = new StateMainFinalService(
            $this->busService,
        );

        foreach ($this->stateHandlerStorage->getStateMainFinal() as $handler) {
            $handler->handle($stateService);
        }
    }
}
