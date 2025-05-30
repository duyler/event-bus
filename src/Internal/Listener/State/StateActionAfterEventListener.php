<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Contract\StateActionInterface;
use Duyler\EventBus\Internal\Event\ActionAfterRunEvent;

class StateActionAfterEventListener
{
    public function __construct(
        private readonly StateActionInterface $stateAction,
    ) {}

    public function __invoke(ActionAfterRunEvent $event): void
    {
        $this->stateAction->after($event->action, $event->result);
    }
}
