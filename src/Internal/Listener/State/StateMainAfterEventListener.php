<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\State;

use Duyler\ActionBus\Contract\StateMainInterface;
use Duyler\ActionBus\Internal\Event\TaskAfterRunEvent;

class StateMainAfterEventListener
{
    public function __construct(private StateMainInterface $stateMain) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        $this->stateMain->after($event->task);
    }
}
