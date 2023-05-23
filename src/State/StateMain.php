<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Contract\State\StateMainAfterHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainFinalHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainStartHandlerInterface;
use Duyler\EventBus\Contract\State\StateMainSuspendHandlerInterface;
use Duyler\EventBus\Control;
use Duyler\EventBus\Enum\StateType;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\Service\StateMainBeforeService;
use Duyler\EventBus\State\Service\StateMainFinalService;
use Duyler\EventBus\State\Service\StateMainStartService;
use Duyler\EventBus\State\Service\StateMainSuspendService;
use Duyler\EventBus\Storage\ActionContainerStorage;
use Duyler\EventBus\Task;

readonly class StateMain
{
    public function __construct(
        private Control                $control,
        private StateHandlerProvider   $stateHandlerProvider,
        private ActionContainerStorage $actionContainerStorage,
    ) {
    }

    public function start(): void
    {
        $stateService = new StateMainStartService(
            $this->control,
        );

        /** @var StateMainStartHandlerInterface $handler */
        foreach ($this->stateHandlerProvider->getHandlers(StateType::MainBeforeStart) as $handler) {
            $handler->handle($stateService);
        }
    }

    public function before(Task $task): void
    {
        $stateService = new StateMainBeforeService(
            $task->action->id,
            $this->control,
        );

        /** @var StateMainBeforeHandlerInterface $handler */
        foreach ($this->stateHandlerProvider->getHandlers(StateType::MainBeforeAction) as $handler) {
            if (empty($handler->observed()) || in_array($task->action->id, $handler->observed())) {
                $handler->handle($stateService);
            }
        }
    }

    public function suspend(Task $task): void
    {
        $handlers = $this->stateHandlerProvider->getHandlers(StateType::MainSuspendAction);

        if ($handlers->isEmpty()) {
            $task->resume($task->getValue());
            return;
        }

        $stateService = new StateMainSuspendService(
            $this->control,
            $task,
            $this->actionContainerStorage->get($task->action->id),
        );

        /** @var StateMainSuspendHandlerInterface $handler */
        foreach ($handlers as $handler) {
            if (empty($handler->observed()) || in_array($task->action->id, $handler->observed())) {
                $handler->handle($stateService);
            }
        }
    }

    public function after(Task $task): void
    {
        $stateService = new StateMainAfterService(
            $task->result->status,
            $task->result->data,
            $task->action->id,
            $this->control,
        );

        /** @var StateMainAfterHandlerInterface $handler */
        foreach ($this->stateHandlerProvider->getHandlers(StateType::MainAfterAction) as $handler) {
            if (empty($handler->observed()) || in_array($task->action->id, $handler->observed())) {
                $handler->handle($stateService);
            }
        }
    }

    public function final(): void
    {
        $stateService = new StateMainFinalService(
            $this->control,
        );

        /** @var StateMainFinalHandlerInterface $handler */
        foreach ($this->stateHandlerProvider->getHandlers(StateType::MainFinal) as $handler) {
            $handler->handle($stateService);
        }
    }
}
