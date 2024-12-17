<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Internal\Event\BusIsResetEvent;
use Duyler\EventBus\Internal\Event\EventRemovedEvent;
use Duyler\EventBus\Internal\Event\TaskQueueIsEmptyEvent;
use Duyler\EventBus\Internal\Listener\Bus\CleanByLimitEventListener;
use Duyler\EventBus\Internal\Listener\Bus\ResetBusEventListener;
use Duyler\EventBus\Internal\Listener\Bus\ResolveActionsAfterEventDeletedEventListener;
use Duyler\DI\Definition;
use Duyler\EventBus\Action\ActionRunnerProvider;
use Duyler\EventBus\Action\ActionSubstitution;
use Duyler\EventBus\Contract\ActionRunnerProviderInterface;
use Duyler\EventBus\Contract\ActionSubstitutionInterface;
use Duyler\EventBus\Contract\StateActionInterface;
use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Enum\Mode;
use Duyler\EventBus\Internal\Event\ActionAfterRunEvent;
use Duyler\EventBus\Internal\Event\ActionBeforeRunEvent;
use Duyler\EventBus\Internal\Event\ActionThrownExceptionEvent;
use Duyler\EventBus\Internal\Event\BusCompletedEvent;
use Duyler\EventBus\Internal\Event\DoCyclicEvent;
use Duyler\EventBus\Internal\Event\DoWhileBeginEvent;
use Duyler\EventBus\Internal\Event\DoWhileEndEvent;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Internal\Event\TaskBeforeRunEvent;
use Duyler\EventBus\Internal\Event\TaskResumeEvent;
use Duyler\EventBus\Internal\Event\TaskSuspendedEvent;
use Duyler\EventBus\Internal\Event\ThrowExceptionEvent;
use Duyler\EventBus\Internal\Event\EventDispatchedEvent;
use Duyler\EventBus\Internal\EventDispatcher;
use Duyler\EventBus\Internal\Listener\Bus\AfterCompleteActionEventListener;
use Duyler\EventBus\Internal\Listener\Bus\DispatchEventEventListener;
use Duyler\EventBus\Internal\Listener\Bus\LogCompleteActionEventListener;
use Duyler\EventBus\Internal\Listener\Bus\ResolveHeldTasksEventListener;
use Duyler\EventBus\Internal\Listener\Bus\ResolveTriggersEventListener;
use Duyler\EventBus\Internal\Listener\Bus\SaveCompleteActionEventListener;
use Duyler\EventBus\Internal\Listener\Bus\TerminateAfterExceptionEventListener;
use Duyler\EventBus\Internal\Listener\Bus\TerminateBusEventListener;
use Duyler\EventBus\Internal\Listener\Bus\ValidateCompleteActionEventListener;
use Duyler\EventBus\Internal\Listener\State\StateActionAfterEventListener;
use Duyler\EventBus\Internal\Listener\State\StateActionBeforeEventListener;
use Duyler\EventBus\Internal\Listener\State\StateActionThrowingEventListener;
use Duyler\EventBus\Internal\Listener\State\StateMainAfterEventListener;
use Duyler\EventBus\Internal\Listener\State\StateMainBeforeEventListener;
use Duyler\EventBus\Internal\Listener\State\StateMainBeginEventListener;
use Duyler\EventBus\Internal\Listener\State\StateMainCyclicEventListener;
use Duyler\EventBus\Internal\Listener\State\StateMainEmptyListener;
use Duyler\EventBus\Internal\Listener\State\StateMainEndEventListener;
use Duyler\EventBus\Internal\Listener\State\StateMainResumeEventListener;
use Duyler\EventBus\Internal\Listener\State\StateMainSuspendEventListener;
use Duyler\EventBus\Internal\ListenerProvider;
use Duyler\EventBus\State\StateAction;
use Duyler\EventBus\State\StateMain;
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
        public readonly bool $allowSkipUnresolvedActions = true,
        public readonly bool $autoreset = false,
        public readonly bool $allowCircularCall = false,
        public readonly int $logMaxSize = 50,
        public readonly Mode $mode = Mode::Queue,
        public readonly bool $continueAfterException = false,
        public readonly int $maxCountCompleteActions = 0,
    ) {
        $this->bind = $this->getBind() + $bind;
    }

    /** @return array<string, string> */
    private function getBind(): array
    {
        return [
            ActionRunnerProviderInterface::class => ActionRunnerProvider::class,
            StateMainInterface::class => StateMain::class,
            StateActionInterface::class => StateAction::class,
            ActionSubstitutionInterface::class => ActionSubstitution::class,
            ListenerProviderInterface::class => ListenerProvider::class,
            EventDispatcherInterface::class => EventDispatcher::class,
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
                SaveCompleteActionEventListener::class,
                CleanByLimitEventListener::class,
                AfterCompleteActionEventListener::class,
                StateMainAfterEventListener::class,
                ResolveTriggersEventListener::class,
                LogCompleteActionEventListener::class,
                ValidateCompleteActionEventListener::class,
                ResolveHeldTasksEventListener::class,
            ],
            TaskQueueIsEmptyEvent::class => [
                StateMainEmptyListener::class,
            ],
            ActionBeforeRunEvent::class => [
                StateActionBeforeEventListener::class,
            ],
            ActionAfterRunEvent::class => [
                StateActionAfterEventListener::class,
            ],
            ActionThrownExceptionEvent::class => [
                StateActionThrowingEventListener::class,
            ],
            EventDispatchedEvent::class => [
                DispatchEventEventListener::class,
            ],
            BusCompletedEvent::class => [
                TerminateBusEventListener::class,
            ],
            ThrowExceptionEvent::class => [
                TerminateAfterExceptionEventListener::class,
            ],
            EventRemovedEvent::class => [
                ResolveActionsAfterEventDeletedEventListener::class,
            ],
            BusIsResetEvent::class => [
                ResetBusEventListener::class,
            ],
        ];
    }
}
