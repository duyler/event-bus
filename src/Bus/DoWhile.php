<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\ActionRunnerProviderInterface;
use Duyler\EventBus\Enum\Mode;
use Duyler\EventBus\Enum\TaskStatus;
use Duyler\EventBus\Internal\Event\DoCyclicEvent;
use Duyler\EventBus\Internal\Event\TaskQueueIsEmptyEvent;
use Duyler\EventBus\Internal\Event\DoWhileBeginEvent;
use Duyler\EventBus\Internal\Event\DoWhileEndEvent;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Internal\Event\TaskBeforeRunEvent;
use Duyler\EventBus\Internal\Event\TaskResumeEvent;
use Duyler\EventBus\Internal\Event\TaskSuspendedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

final class DoWhile
{
    public function __construct(
        private ActionRunnerProviderInterface $actionRunnerProvider,
        private TaskQueue $taskQueue,
        private EventDispatcherInterface $eventDispatcher,
        private BusConfig $busConfig,
    ) {}

    public function run(): void
    {
        $this->eventDispatcher->dispatch(new DoWhileBeginEvent());

        do {
            $this->eventDispatcher->dispatch(new DoCyclicEvent());

            if ($this->taskQueue->isEmpty() && Mode::Loop === $this->busConfig->mode) {
                continue;
            }

            $task = $this->taskQueue->dequeue();

            if ($task->isRunning()) {
                $this->eventDispatcher->dispatch(new TaskResumeEvent($task));
                $this->process($task);
                continue;
            }

            $this->eventDispatcher->dispatch(new TaskBeforeRunEvent($task));

            if (false === $task->isRejected()) {
                if (TaskStatus::Primary === $task->getStatus()) {
                    $task->run($this->actionRunnerProvider->getRunner($task->action));
                } else {
                    $task->retry();
                }
            }

            $this->process($task);
        } while (Mode::Loop === $this->busConfig->mode || $this->taskQueue->isNotEmpty());

        $this->eventDispatcher->dispatch(new DoWhileEndEvent());
    }

    private function process(Task $task): void
    {
        if ($task->isRunning()) {
            $this->taskQueue->push($task);
            $this->eventDispatcher->dispatch(new TaskSuspendedEvent($task));
        } else {
            $this->eventDispatcher->dispatch(new TaskAfterRunEvent($task));
            if ($this->taskQueue->isEmpty()) {
                $this->eventDispatcher->dispatch(new TaskQueueIsEmptyEvent());
            }
        }
    }
}
