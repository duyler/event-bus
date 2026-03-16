<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\ActionRunnerProviderInterface;
use Duyler\EventBus\Contract\ErrorHandlerInterface;
use Duyler\EventBus\Contract\LoopInterface;
use Duyler\EventBus\Contract\ResourceInterface;
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
use EvIo;
use EvTimer;
use EvWatcher;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Throwable;

final class DoWhile implements LoopInterface
{
    private const float DELAY_BEFORE_RUN = 0.001;

    private EvWatcher $watcher;

    public function __construct(
        private readonly ActionRunnerProviderInterface $actionRunnerProvider,
        private readonly TaskQueue $taskQueue,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly BusConfig $busConfig,
        private readonly ErrorHandlerInterface $errorHandler,
        private readonly State $state,
    ) {
        $repeat = $this->busConfig->tickInterval / 1000;
        $this->watcher = new EvTimer(self::DELAY_BEFORE_RUN, $repeat, function (): void {
            $this->tick();
        });
    }

    #[Override]
    public function run(): void
    {
        $this->eventDispatcher->dispatch(new DoWhileBeginEvent());

        if (Mode::Queue === $this->busConfig->mode && $this->taskQueue->isEmpty()) {
            throw new RuntimeException('TaskQueue is empty');
        }

        $this->watcher->is_active or $this->watcher->start();
        Ev::run();
    }

    private function tick(): void
    {
        while (true) {
            if (Mode::Queue === $this->busConfig->mode && $this->taskQueue->isEmpty()) {
                $this->watcher->stop();
                Ev::stop(Ev::BREAK_ALL);
                $this->eventDispatcher->dispatch(new DoWhileEndEvent());
                return;
            }

            $this->eventDispatcher->dispatch(new DoCyclicEvent());

            if (Mode::Loop === $this->busConfig->mode && $this->taskQueue->isEmpty()) {
                return;
            }

            $task = $this->taskQueue->dequeue();

            if ($task->isRunning()) {
                $this->eventDispatcher->dispatch(new TaskResumeEvent($task));
                $this->process($task);
                continue;
            }

            $this->eventDispatcher->dispatch(new TaskBeforeRunEvent($task));

            if ($task->isRejected()) {
                continue;
            }

            try {
                if (TaskStatus::Primary === $task->getStatus()) {
                    $task->run($this->actionRunnerProvider->getRunner($task->action));
                } elseif (TaskStatus::Retry === $task->getStatus()) {
                    if (false === $task->isReady()) {
                        $this->taskQueue->push($task);
                        continue;
                    }
                    $task->retry();
                }

                $this->process($task);
            } catch (Throwable $e) {
                $this->errorHandler->handle($e, $this->state->getLog());
            }

            if ($this->taskQueue->isEmpty()) {
                return;
            }
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

    #[Override]
    public function stop(): void
    {
        $this->watcher->stop();
        Ev::stop(Ev::BREAK_ALL);
    }

    public function setResource(ResourceInterface $resource): void
    {
        $stream = $resource->getResource();

        if (null !== $stream) {

            stream_set_blocking($stream, false);

            $this->watcher = new EvIo($stream, Ev::READ, function () use ($stream): void {
                fread($stream, 4096);
                $this->watcher->stop();
                $this->tick();
                $this->watcher->start();
            });
        }
    }
}
