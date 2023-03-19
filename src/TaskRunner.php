<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Action\ActionHandler;

class TaskRunner
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
        private readonly ActionHandler $actionHandler,
        private readonly TaskQueue $taskQueue
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
            $this->taskQueue->add($task);
        } else {
            $task->takeResult();
            $this->dispatcher->dispatchResultEvent($task);
        }
    }
}
