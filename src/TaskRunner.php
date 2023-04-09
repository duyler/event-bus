<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Action\ActionHandler;

readonly class TaskRunner
{
    public function __construct(
        private Dispatcher    $dispatcher,
        private ActionHandler $actionHandler,
        private TaskQueue     $taskQueue
    ) {
    }

    public function run(Task $task): void
    {
        $task->run($this->actionHandler);
        $this->dispatch($task);
    }

    public function resume(Task $task): void
    {
        if ($task->isRunning()) {
            $task->resume();
            $this->dispatch($task);
        }
    }

    private function dispatch(Task $task): void
    {
        if ($task->isRunning()) {
            $this->taskQueue->push($task);
        } else {
            $task->takeResult();
            $this->dispatcher->dispatchResultTask($task);
        }
    }
}
