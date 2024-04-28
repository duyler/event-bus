<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Bus;

use Duyler\ActionBus\BusConfig;
use Duyler\ActionBus\Contract\ActionRunnerProviderInterface;
use Duyler\ActionBus\Enum\Mode;
use Duyler\ActionBus\Internal\Event\DoCyclicEvent;
use Duyler\ActionBus\Internal\Event\DoWhileBeginEvent;
use Duyler\ActionBus\Internal\Event\DoWhileEndEvent;
use Duyler\ActionBus\Internal\Event\TaskAfterRunEvent;
use Duyler\ActionBus\Internal\Event\TaskBeforeRunEvent;
use Duyler\ActionBus\Internal\Event\TaskResumeEvent;
use Duyler\ActionBus\Internal\Event\TaskSuspendedEvent;
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
            $task->run($this->actionRunnerProvider->getRunner($task->action));
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
        }
    }
}
