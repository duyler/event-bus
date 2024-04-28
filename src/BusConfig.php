<?php

declare(strict_types=1);

namespace Duyler\ActionBus;

use Duyler\DependencyInjection\Definition;
use Duyler\ActionBus\Action\ActionRunnerProvider;
use Duyler\ActionBus\Action\ActionSubstitution;
use Duyler\ActionBus\Contract\ActionRunnerProviderInterface;
use Duyler\ActionBus\Contract\ActionSubstitutionInterface;
use Duyler\ActionBus\Contract\StateActionInterface;
use Duyler\ActionBus\Contract\StateMainInterface;
use Duyler\ActionBus\Enum\Mode;
use Duyler\ActionBus\Enum\ResetMode;
use Duyler\ActionBus\Internal\Event\ActionAfterRunEvent;
use Duyler\ActionBus\Internal\Event\ActionBeforeRunEvent;
use Duyler\ActionBus\Internal\Event\ActionThrownExceptionEvent;
use Duyler\ActionBus\Internal\Event\BusCompletedEvent;
use Duyler\ActionBus\Internal\Event\DoCyclicEvent;
use Duyler\ActionBus\Internal\Event\DoWhileBeginEvent;
use Duyler\ActionBus\Internal\Event\DoWhileEndEvent;
use Duyler\ActionBus\Internal\Event\TaskAfterRunEvent;
use Duyler\ActionBus\Internal\Event\TaskBeforeRunEvent;
use Duyler\ActionBus\Internal\Event\TaskResumeEvent;
use Duyler\ActionBus\Internal\Event\TaskSuspendedEvent;
use Duyler\ActionBus\Internal\Event\ThrowExceptionEvent;
use Duyler\ActionBus\Internal\Event\TriggerPushedEvent;
use Duyler\ActionBus\Internal\EventDispatcher;
use Duyler\ActionBus\Internal\Listener\Bus\CompleteActionEventListener;
use Duyler\ActionBus\Internal\Listener\Bus\DispatchTriggerEventListener;
use Duyler\ActionBus\Internal\Listener\Bus\LogCompleteActionEventListener;
use Duyler\ActionBus\Internal\Listener\Bus\ResolveHeldTasksEventListener;
use Duyler\ActionBus\Internal\Listener\Bus\ResolveSubscriptionsEventListener;
use Duyler\ActionBus\Internal\Listener\Bus\TerminateAfterExceptionEventListener;
use Duyler\ActionBus\Internal\Listener\Bus\TerminateBusEventListener;
use Duyler\ActionBus\Internal\Listener\Bus\ValidateCompleteActionEventListener;
use Duyler\ActionBus\Internal\Listener\State\StateActionAfterEventListener;
use Duyler\ActionBus\Internal\Listener\State\StateActionBeforeEventListener;
use Duyler\ActionBus\Internal\Listener\State\StateActionThrowingEventListener;
use Duyler\ActionBus\Internal\Listener\State\StateMainAfterEventListener;
use Duyler\ActionBus\Internal\Listener\State\StateMainBeforeEventListener;
use Duyler\ActionBus\Internal\Listener\State\StateMainBeginEventListener;
use Duyler\ActionBus\Internal\Listener\State\StateMainCyclicEventListener;
use Duyler\ActionBus\Internal\Listener\State\StateMainEndEventListener;
use Duyler\ActionBus\Internal\Listener\State\StateMainResumeEventListener;
use Duyler\ActionBus\Internal\Listener\State\StateMainSuspendEventListener;
use Duyler\ActionBus\Internal\ListenerProvider;
use Duyler\ActionBus\State\StateAction;
use Duyler\ActionBus\State\StateMain;
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
        public readonly bool $saveStateActionContainer = false,
        public readonly bool $allowSkipUnresolvedActions = true,
        public readonly bool $autoreset = false,
        public readonly bool $allowCircularCall = false,
        public readonly int $logMaxSize = 50,
        public readonly Mode $mode = Mode::Queue,
        public readonly ResetMode $resetMode = ResetMode::Soft,
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
                CompleteActionEventListener::class,
                StateMainAfterEventListener::class,
                ResolveSubscriptionsEventListener::class,
                LogCompleteActionEventListener::class,
                ValidateCompleteActionEventListener::class,
                ResolveHeldTasksEventListener::class,
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
            TriggerPushedEvent::class => [
                DispatchTriggerEventListener::class,
            ],
            BusCompletedEvent::class => [
                TerminateBusEventListener::class,
            ],
            ThrowExceptionEvent::class => [
                TerminateAfterExceptionEventListener::class,
            ],
        ];
    }
}
