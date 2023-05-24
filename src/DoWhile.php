<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Action\ActionHandler;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\StateType;

readonly class DoWhile
{
    public function __construct(
        private Dispatcher    $dispatcher,
        private ActionHandler $actionHandler,
        private TaskQueue     $taskQueue,
        private State         $state,
    ) {
    }

    public function run(): void
    {
        $this->state->declare(StateType::MainBeforeStart);

        do {
            /** @var Task $task */
            $task = $this->taskQueue->dequeue();

            if ($task->isRunning()) {
                $this->state->declare(StateType::MainSuspendAction, $task);
                $this->dispatch($task);
                continue;
            }

            $this->state->declare(StateType::MainBeforeAction, $task);
            $this->runTask($task);
        } while ($this->taskQueue->isNotEmpty());

        $this->state->declare(StateType::MainFinal);
    }

    public function runTask(Task $task): void
    {
        $task->run(fn(): Result => $this->actionHandler->handle($task->action));
        $this->dispatch($task);
    }

    private function dispatch(Task $task): void
    {
        if ($task->isRunning()) {
            $this->taskQueue->push($task);
        } else {
            $task->takeResult();
            $this->state->declare(StateType::MainAfterAction, $task);
            $this->dispatcher->dispatchResultTask($task);
        }
    }
}
