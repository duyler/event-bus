<?php

declare(strict_types=1);

namespace Konveyer\EventBus;

class TaskRunner
{
    private Dispatcher $dispatcher;
    private ActionHandler $actionHandler;
    private TaskQueue $taskQueue;

    public function __construct(
        Dispatcher $dispatcher,
        ActionHandler $actionHandler,
        TaskQueue $taskQueue
    ) {
        $this->dispatcher = $dispatcher;
        $this->actionHandler = $actionHandler;
        $this->taskQueue = $taskQueue;
    }

    public function run(Task $task): void
    {
        $this->actionHandler->prepare($task->action);
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
