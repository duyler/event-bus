<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\ActionRunnerProviderInterface;
use Duyler\EventBus\Contract\ErrorHandlerInterface;
use Duyler\EventBus\Enum\Mode;
use Duyler\EventBus\Enum\TaskStatus;
use Duyler\EventBus\Internal\Event\DoCyclicEvent;
use Duyler\EventBus\Internal\Event\DoWhileBeginEvent;
use Duyler\EventBus\Internal\Event\DoWhileEndEvent;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Internal\Event\TaskBeforeRunEvent;
use Duyler\EventBus\Internal\Event\TaskQueueIsEmptyEvent;
use Duyler\EventBus\Internal\Event\TaskResumeEvent;
use Duyler\EventBus\Internal\Event\TaskSuspendedEvent;
use Ev;
use EvTimer;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

final readonly class DoWhile
{
    private EvTimer $timer;

    public function __construct(
        private ActionRunnerProviderInterface $actionRunnerProvider,
        private TaskQueue $taskQueue,
        private EventDispatcherInterface $eventDispatcher,
        private BusConfig $busConfig,
        private ErrorHandlerInterface $errorHandler,
        private State $state,
    ) {
        $repeat = $this->busConfig->tickInterval / 1000;
        $this->timer = new EvTimer(0.001, $repeat, function () {
            $this->tick();
        });
    }

    public function run(): void
    {
        $this->eventDispatcher->dispatch(new DoWhileBeginEvent());

        Ev::run();
    }

    private function tick(): void
    {
        $this->eventDispatcher->dispatch(new DoCyclicEvent());

        if ($this->taskQueue->isEmpty() && Mode::Loop === $this->busConfig->mode) {
            return;
        }

        $task = $this->taskQueue->dequeue();

        if ($task->isRunning()) {
            $this->eventDispatcher->dispatch(new TaskResumeEvent($task));
            $this->process($task);
            return;
        }

        $this->eventDispatcher->dispatch(new TaskBeforeRunEvent($task));

        if ($task->isRejected()) {
            return;
        }

        try {
            if (TaskStatus::Primary === $task->getStatus()) {
                $task->run($this->actionRunnerProvider->getRunner($task->action));
            } elseif (TaskStatus::Retry === $task->getStatus()) {
                if (false === $task->isReady()) {
                    $this->taskQueue->push($task);
                    return;
                }
                $task->retry();
            }

            $this->process($task);

            if (Mode::Queue === $this->busConfig->mode && $this->taskQueue->isEmpty()) {
                $this->timer->stop();
                $this->eventDispatcher->dispatch(new DoWhileEndEvent());
            }
        } catch (Throwable $e) {
            $this->errorHandler->handle($e, $this->state->getLog());
        }
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
