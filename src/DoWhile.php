<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Action\ActionHandler;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Exception\CircularCallActionException;
use Duyler\EventBus\Exception\ConsecutiveRepeatedActionException;
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

    /**
     * @throws ConsecutiveRepeatedActionException
     * @throws CircularCallActionException
     */
    public function run(): void
    {
        $this->stateMain->start();

        do {
            $task = $this->taskQueue->dequeue();

            if ($task->isRunning()) {
                $this->stateMain->resume($task);
                $this->dispatch($task);
                continue;
            }

            $this->stateMain->before($task);
            $this->runTask($task);
        } while ($this->taskQueue->isNotEmpty());

        $this->stateMain->final();
    }

    /**
     * @throws ConsecutiveRepeatedActionException
     * @throws CircularCallActionException
     */
    public function runTask(Task $task): void
    {
        $task->run(fn(): Result => $this->actionHandler->handle($task->action));
        $this->dispatch($task);
    }

    /**
     * @throws ConsecutiveRepeatedActionException
     * @throws CircularCallActionException
     */
    private function dispatch(Task $task): void
    {
        if ($task->isRunning()) {
            $this->taskQueue->push($task);
            $this->stateMain->suspend($task);
        } else {
            $task->takeResult();
            $this->dispatcher->dispatchResultTask($task);
            $this->stateMain->after($task);
        }
    }
}
