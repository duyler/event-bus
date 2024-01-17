<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Contract\ActionRunnerInterface;
use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Exception\CircularCallActionException;
use Duyler\EventBus\Exception\ConsecutiveRepeatedActionException;

class DoWhile
{
    public function __construct(
        private Publisher $publisher,
        private ActionRunnerInterface $actionRunner,
        private TaskQueue $taskQueue,
        private StateMainInterface $stateMain,
    ) {}

    /**
     * @throws ConsecutiveRepeatedActionException
     * @throws CircularCallActionException
     */
    public function run(): void
    {
        $this->stateMain->begin();

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
        $task->run(fn(): Result => $this->actionRunner->runAction($task->action));
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
            $this->publisher->publish($task);
            $this->stateMain->after($task);
        }
    }
}
