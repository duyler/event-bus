<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\State;

use Duyler\ActionBus\Contract\StateActionInterface;
use Duyler\ActionBus\Internal\Event\ActionBeforeRunEvent;

class StateActionBeforeEventListener
{
    public function __construct(
        private StateActionInterface $stateAction,
    ) {}

    public function __invoke(ActionBeforeRunEvent $event): void
    {
        $this->stateAction->before($event->action, $event->argument);
    }
}
