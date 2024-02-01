<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Contract\StateActionInterface;
use Duyler\EventBus\Internal\Event\ActionBeforeRunEvent;

class StateActionBeforeEventListener
{
    public function __construct(
        private StateActionInterface $stateAction,
    ) {}

    public function __invoke(ActionBeforeRunEvent $event): void
    {
        $this->stateAction->before($event->action);
    }
}
