<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\State;

use Duyler\ActionBus\Contract\StateActionInterface;
use Duyler\ActionBus\Internal\Event\ActionThrownExceptionEvent;

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
