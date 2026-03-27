<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\DI\Definition;
use Duyler\EventBus\Actor\ActorRunnerProvider;
use Duyler\EventBus\Actor\ActorSubstitution;
use Duyler\EventBus\Bus\DoWhile;
use Duyler\EventBus\Contract\ActorRunnerProviderInterface;
use Duyler\EventBus\Contract\ActorSubstitutionInterface;
use Duyler\EventBus\Contract\LoopInterface;
use Duyler\EventBus\Contract\StateActorInterface;
use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Enum\Mode;
use Duyler\EventBus\Internal\Event\ActorAddedEvent;
use Duyler\EventBus\Internal\Event\ActorAfterRunEvent;
use Duyler\EventBus\Internal\Event\ActorBeforeRunEvent;
use Duyler\EventBus\Internal\Event\ActorRemovedEvent;
use Duyler\EventBus\Internal\Event\ActorThrownExceptionEvent;
use Duyler\EventBus\Internal\Event\BusCompletedEvent;
use Duyler\EventBus\Internal\Event\BusIsResetEvent;
use Duyler\EventBus\Internal\Event\DoCyclicEvent;
use Duyler\EventBus\Internal\Event\DoWhileBeginEvent;
use Duyler\EventBus\Internal\Event\DoWhileEndEvent;
use Duyler\EventBus\Internal\Event\EventAddedEvent;
use Duyler\EventBus\Internal\Event\EventDispatchedEvent;
use Duyler\EventBus\Internal\Event\EventRemovedEvent;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Internal\Event\TaskBeforeRunEvent;
use Duyler\EventBus\Internal\Event\TaskQueueIsEmptyEvent;
use Duyler\EventBus\Internal\Event\TaskResumeEvent;
use Duyler\EventBus\Internal\Event\TaskSuspendedEvent;
use Duyler\EventBus\Internal\Event\TaskUnresolvedEvent;
use Duyler\EventBus\Internal\Event\ThrowExceptionEvent;
use Duyler\EventBus\Internal\EventDispatcher;
use Duyler\EventBus\Internal\Listener\Bus\AfterCompleteActorEventListener;
use Duyler\EventBus\Internal\Listener\Bus\AutoresetEventListener;
use Duyler\EventBus\Internal\Listener\Bus\CleanByLimitEventListener;
use Duyler\EventBus\Internal\Listener\Bus\DispatchActorEventEventListener;
use Duyler\EventBus\Internal\Listener\Bus\DispatchEventEventListener;
use Duyler\EventBus\Internal\Listener\Bus\LogCompleteActorEventListener;
use Duyler\EventBus\Internal\Listener\Bus\ResetBusEventListener;
use Duyler\EventBus\Internal\Listener\Bus\ResolveActorsAfterEventDeletedEventListener;
use Duyler\EventBus\Internal\Listener\Bus\ResolveHeldTasksEventListener;
use Duyler\EventBus\Internal\Listener\Bus\SaveCompleteActorEventListener;
use Duyler\EventBus\Internal\Listener\Bus\SchedulerTickEventListener;
use Duyler\EventBus\Internal\Listener\Bus\TerminateAfterExceptionEventListener;
use Duyler\EventBus\Internal\Listener\Bus\ValidateCompleteActorEventListener;
use Duyler\EventBus\Internal\Listener\State\StateActorAfterEventListener;
use Duyler\EventBus\Internal\Listener\State\StateActorBeforeEventListener;
use Duyler\EventBus\Internal\Listener\State\StateActorThrowingEventListener;
use Duyler\EventBus\Internal\Listener\State\StateMainAfterEventListener;
use Duyler\EventBus\Internal\Listener\State\StateMainBeforeEventListener;
use Duyler\EventBus\Internal\Listener\State\StateMainBeginEventListener;
use Duyler\EventBus\Internal\Listener\State\StateMainCyclicEventListener;
use Duyler\EventBus\Internal\Listener\State\StateMainEmptyListener;
use Duyler\EventBus\Internal\Listener\State\StateMainEndEventListener;
use Duyler\EventBus\Internal\Listener\State\StateMainResumeEventListener;
use Duyler\EventBus\Internal\Listener\State\StateMainSuspendEventListener;
use Duyler\EventBus\Internal\Listener\State\StateMainUnresolvedEventListener;
use Duyler\EventBus\Internal\ListenerProvider;
use Duyler\EventBus\State\StateActor;
use Duyler\EventBus\State\StateMain;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class BusConfig
{
    /** @var array<string, string> */
    public readonly array $bind;

    /** @param array<string, string> $bind */
    public function __construct(
        array $bind = [],

        /** @var array<string, string> */
        public readonly array $providers = [],

        /** @var Definition[] */
        public readonly array $definitions = [],
        public readonly bool $allowSkipUnresolvedActors = true,
        public readonly bool $autoreset = false,
        public readonly bool $allowCircularCall = false,
        public readonly int $logMaxSize = 50,
        public readonly Mode $mode = Mode::Queue,
        public readonly bool $continueAfterException = false,
        public readonly int $maxCountCompleteActors = 0,
        public readonly int $maxCountEvents = 0,
        public readonly int $tickInterval = 1,
        public readonly int $schedulerCheckInterval = 100,
        public readonly int $gcCollectCyclesInterval = 120000,
        public readonly int $gcMemCachesInterval = 60000,
    ) {
        $this->bind = $this->getBind() + $bind;

        if ($this->tickInterval < 1) {
            throw new InvalidArgumentException('Tick interval must be greater than 0');
        }
    }

    /** @return array<string, string> */
    private function getBind(): array
    {
        return [
            ActorRunnerProviderInterface::class => ActorRunnerProvider::class,
            StateMainInterface::class => StateMain::class,
            StateActorInterface::class => StateActor::class,
            ActorSubstitutionInterface::class => ActorSubstitution::class,
            ListenerProviderInterface::class => ListenerProvider::class,
            EventDispatcherInterface::class => EventDispatcher::class,
            LoopInterface::class => DoWhile::class,
        ];
    }

    /** @return array<string, string[]> */
    public function getListeners(): array
    {
        return [
            DoWhileBeginEvent::class => [
                StateMainBeginEventListener::class,
            ],
            DoCyclicEvent::class => [
                StateMainCyclicEventListener::class,
                SchedulerTickEventListener::class,
            ],
            DoWhileEndEvent::class => [
                StateMainEndEventListener::class,
            ],
            TaskBeforeRunEvent::class => [
                StateMainBeforeEventListener::class,
            ],
            TaskResumeEvent::class => [
                StateMainResumeEventListener::class,
            ],
            TaskSuspendedEvent::class => [
                StateMainSuspendEventListener::class,
            ],
            TaskAfterRunEvent::class => [
                SaveCompleteActorEventListener::class,
                DispatchActorEventEventListener::class,
                CleanByLimitEventListener::class,
                AfterCompleteActorEventListener::class,
                LogCompleteActorEventListener::class,
                StateMainAfterEventListener::class,
                ValidateCompleteActorEventListener::class,
                ResolveHeldTasksEventListener::class,
            ],
            TaskQueueIsEmptyEvent::class => [
                StateMainEmptyListener::class,
                AutoresetEventListener::class,
            ],
            TaskUnresolvedEvent::class => [
                StateMainUnresolvedEventListener::class,
            ],
            ActorBeforeRunEvent::class => [
                StateActorBeforeEventListener::class,
            ],
            ActorAfterRunEvent::class => [
                StateActorAfterEventListener::class,
            ],
            ActorThrownExceptionEvent::class => [
                StateActorThrowingEventListener::class,
            ],
            EventDispatchedEvent::class => [
                DispatchEventEventListener::class,
            ],
            BusCompletedEvent::class => [

            ],
            ThrowExceptionEvent::class => [
                TerminateAfterExceptionEventListener::class,
            ],
            EventRemovedEvent::class => [
                ResolveActorsAfterEventDeletedEventListener::class,
            ],
            BusIsResetEvent::class => [
                ResetBusEventListener::class,
            ],
        ];
    }

    /**
     * @return class-string[]
     */
    public function getExternalAllowedEvents(): array
    {
        return [
            ThrowExceptionEvent::class,
            ActorAddedEvent::class,
            EventAddedEvent::class,
            ActorRemovedEvent::class,
            EventRemovedEvent::class,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'bind' => $this->bind,
            'providers' => $this->providers,
            'definitions' => $this->definitions,
            'allowSkipUnresolvedActors' => $this->allowSkipUnresolvedActors,
            'autoreset' => $this->autoreset,
            'allowCircularCall' => $this->allowCircularCall,
            'logMaxSize' => $this->logMaxSize,
            'mode' => $this->mode,
            'continueAfterException' => $this->continueAfterException,
            'maxCountCompleteActors' => $this->maxCountCompleteActors,
            'maxCountEvents' => $this->maxCountEvents,
            'tickInterval' => $this->tickInterval,
            'schedulerCheckInterval' => $this->schedulerCheckInterval,
            'gcCollectCyclesInterval' => $this->gcCollectCyclesInterval,
            'gcMemCachesInterval' => $this->gcMemCachesInterval,
        ];
    }
}
