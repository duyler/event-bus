<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Action\ActionHandler;
use Duyler\EventBus\Dto\Result;
use Closure;

class DoWhile
{
    public function __construct(
        private readonly Dispatcher    $dispatcher,
        private readonly ActionHandler $actionHandler,
        private readonly TaskQueue $taskQueue,
        private readonly State $state,
    ) {
    }

    public function run(): void
    {
        $this->state->start();
        do {
            $task = $this->taskQueue->dequeue();
            if ($task->isRunning()) {
                $this->resume($task);
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
        $this->dispatch($task);
    }

    private function dispatch(Task $task): void
    {
        if ($task->isRunning()) {
            $this->taskQueue->push($task);
        } else {
            $task->takeResult();
            $this->state->after($task);
            $this->dispatcher->dispatchResultTask($task);
        }
    }

    public function resume(Task $task): void
    {
        if ($task->isRunning()) {
            $task->resume(
                fn(mixed $value, Closure $callback): mixed
                    => $this->actionHandler->handleCoroutine($task->action, $value, $callback)
            );
            $this->dispatch($task);
        }
    }
}
