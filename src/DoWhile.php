<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Action\ActionHandler;
use Duyler\EventBus\Dto\Result;

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
        $this->state->start();
        do {
            /** @var Task $task */
            $task = $this->taskQueue->dequeue();
            if ($task->isRunning()) {
                $task->resume();
                $this->dispatch($task);
                continue;
            }
            $this->state->before($task);
            $this->runTask($task);
        } while ($this->taskQueue->isNotEmpty());
        $this->state->final();
    }

    public function runTask(Task $task): void
    {
        $task->run(fn(): Result => $this->actionHandler->handle($task->action));
        if ($task->isRunning() && $task->coroutine !== null) {
            $this->actionHandler->handleCoroutine($task->action, $task->coroutine, $task->getValue());
        }
        $this->dispatch($task);
    }

    private function dispatch(Task $task): void
    {
        if ($task->isRunning()) {
            $this->state->suspend($task);
            $this->taskQueue->push($task);
        } else {
            $task->takeResult();
            $this->state->after($task);
            $this->dispatcher->dispatchResultTask($task);
        }
    }
}
