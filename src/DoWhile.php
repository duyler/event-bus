<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Action\ActionHandler;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\State\StateMain;

readonly class DoWhile
{
    public function __construct(
        private Dispatcher    $dispatcher,
        private ActionHandler $actionHandler,
        private TaskQueue     $taskQueue,
        private StateMain     $stateMain,
    ) {
    }

    public function run(): void
    {
        $this->stateMain->start();

        do {
            $task = $this->taskQueue->dequeue();

            if ($task->isRunning()) {
                $this->stateMain->suspend($task);
                $this->dispatch($task);
                continue;
            }

            $this->stateMain->before($task);
            $this->runTask($task);
        } while ($this->taskQueue->isNotEmpty());

        $this->stateMain->final();
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
            $this->stateMain->after($task);
            $this->dispatcher->dispatchResultTask($task);
        }
    }
}
