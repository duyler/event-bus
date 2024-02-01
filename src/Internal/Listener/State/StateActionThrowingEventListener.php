<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Contract\StateActionInterface;
use Duyler\EventBus\Internal\Event\ActionThrownExceptionEvent;

class StateActionThrowingEventListener
{
    public function __construct(
        private StateActionInterface $stateAction,
    ) {}

    public function __invoke(ActionThrownExceptionEvent $event): void
    {
        $this->stateAction->throwing($event->action, $event->exception);
    }
}
