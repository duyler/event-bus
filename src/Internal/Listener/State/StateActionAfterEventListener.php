<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\State;

use Duyler\ActionBus\Contract\StateActionInterface;
use Duyler\ActionBus\Internal\Event\ActionAfterRunEvent;

class StateActionAfterEventListener
{
    public function __construct(
        private StateActionInterface $stateAction,
    ) {}

    public function __invoke(ActionAfterRunEvent $event): void
    {
        $this->stateAction->after($event->action, $event->result);
    }
}
