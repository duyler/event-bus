<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Action\ActionRunner;
use Duyler\EventBus\Action\ActionSubstitution;
use Duyler\EventBus\Contract\ActionRunnerInterface;
use Duyler\EventBus\Contract\ActionSubstitutionInterface;
use Duyler\EventBus\Contract\StateActionInterface;
use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Internal\Event\ActionAfterRunEvent;
use Duyler\EventBus\Internal\Event\ActionBeforeRunEvent;
use Duyler\EventBus\Internal\Event\ActionIsCompleteEvent;
use Duyler\EventBus\Internal\Event\ActionThrownExceptionEvent;
use Duyler\EventBus\Internal\Event\DoWhileBeginEvent;
use Duyler\EventBus\Internal\Event\DoWhileEndEvent;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Internal\Event\TaskBeforeRunEvent;
use Duyler\EventBus\Internal\Event\TaskResumeEvent;
use Duyler\EventBus\Internal\Event\TaskSuspendedEvent;
use Duyler\EventBus\Internal\EventDispatcher;
use Duyler\EventBus\Internal\Listener\Bus\BindContractCompleteActionEventListener;
use Duyler\EventBus\Internal\Listener\Bus\LogCompleteActionEventListener;
use Duyler\EventBus\Internal\Listener\Bus\ResolveCompleteActionSubscriptionsEventListener;
use Duyler\EventBus\Internal\Listener\Bus\ResolveHeldTasksEventListener;
use Duyler\EventBus\Internal\Listener\Bus\ValidateCompleteActionEventListener;
use Duyler\EventBus\Internal\Listener\State\StateActionAfterEventListener;
use Duyler\EventBus\Internal\Listener\State\StateActionBeforeEventListener;
use Duyler\EventBus\Internal\Listener\State\StateActionThrowingEventListener;
use Duyler\EventBus\Internal\Listener\State\StateMainAfterEventListener;
use Duyler\EventBus\Internal\Listener\State\StateMainBeforeEventListener;
use Duyler\EventBus\Internal\Listener\State\StateMainBeginEventListener;
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
    public readonly array $bind;

    public function __construct(
        array $bind = [],
        public readonly array $providers = [],
        public readonly array $definitions = [],
        public readonly bool $enableTriggers = true,
    ) {
        $this->bind = $this->getBind() + $bind;
    }

    private function getBind(): array
    {
        return [
            ActionRunnerInterface::class => ActionRunner::class,
            StateMainInterface::class => StateMain::class,
            StateActionInterface::class => StateAction::class,
            ActionSubstitutionInterface::class => ActionSubstitution::class,
            ListenerProviderInterface::class => ListenerProvider::class,
            EventDispatcherInterface::class => EventDispatcher::class,
        ];
    }

    public function getListeners(): array
    {
        return [
            ActionIsCompleteEvent::class => [
                BindContractCompleteActionEventListener::class,
                LogCompleteActionEventListener::class,
                ValidateCompleteActionEventListener::class,
                ResolveCompleteActionSubscriptionsEventListener::class,
                ResolveHeldTasksEventListener::class,
            ],
            DoWhileBeginEvent::class => [
                StateMainBeginEventListener::class,
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
                StateMainAfterEventListener::class,
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
        ];
    }
}
