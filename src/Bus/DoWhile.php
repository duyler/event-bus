<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Contract\ActionRunnerInterface;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Internal\Event\DoWhileBeginEvent;
use Duyler\EventBus\Internal\Event\DoWhileEndEvent;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Internal\Event\TaskBeforeRunEvent;
use Duyler\EventBus\Internal\Event\TaskResumeEvent;
use Duyler\EventBus\Internal\Event\TaskSuspendedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

class DoWhile
{
    public function __construct(
        private Publisher $publisher,
        private ActionRunnerInterface $actionRunner,
        private TaskQueue $taskQueue,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function run(): void
    {
        $this->eventDispatcher->dispatch(new DoWhileBeginEvent());

        do {
            $task = $this->taskQueue->dequeue();

            if ($task->isRunning()) {
                $this->eventDispatcher->dispatch(new TaskResumeEvent($task));
                $this->process($task);
                continue;
            }

            $this->eventDispatcher->dispatch(new TaskBeforeRunEvent($task));
            $this->runTask($task);
        } while ($this->taskQueue->isNotEmpty());

        $this->eventDispatcher->dispatch(new DoWhileEndEvent());
    }

    public function runTask(Task $task): void
    {
        $task->run(fn(): Result => $this->actionRunner->runAction($task->action));
        $this->process($task);
    }

    private function process(Task $task): void
    {
        if ($task->isRunning()) {
            $this->taskQueue->push($task);
            $this->eventDispatcher->dispatch(new TaskSuspendedEvent($task));
        } else {
            $this->publisher->publish($task);
            $this->eventDispatcher->dispatch(new TaskAfterRunEvent($task));
        }
    }
}
