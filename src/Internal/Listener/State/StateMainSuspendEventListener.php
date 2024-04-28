<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\State;

use Duyler\ActionBus\Contract\StateMainInterface;
use Duyler\ActionBus\Internal\Event\TaskSuspendedEvent;

class StateMainSuspendEventListener
{
    public function __construct(private StateMainInterface $stateMain) {}

    public function __invoke(TaskSuspendedEvent $event): void
    {
        $this->stateMain->suspend($event->task);
    }
}
