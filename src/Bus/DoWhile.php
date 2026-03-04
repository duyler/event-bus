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
use Socket;
use Throwable;

final class DoWhile implements LoopInterface
{
    private const float DELAY_BEFORE_RUN = 0.001;

    private ?EvTimer $timer = null;

    private ?EvWatcher $watcher = null;

    private ?ResourceInterface $ioResource = null;

    public function __construct(
        private ActionRunnerProviderInterface $actionRunnerProvider,
        private TaskQueue $taskQueue,
        private EventDispatcherInterface $eventDispatcher,
        private BusConfig $busConfig,
        private ErrorHandlerInterface $errorHandler,
        private State $state,
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
        $this->ioResource = $resource;

        /** @var Socket $socket */
        $socket = $this->ioResource->getResource();

        if (null !== $socket) {

            $stream = socket_export_stream($socket);

            stream_set_blocking($stream, false);

            $this->watcher = new EvIo($stream, Ev::READ, function () {
                $this->tick();
            });
        }
    }
}
