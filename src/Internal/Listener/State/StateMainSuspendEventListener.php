<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Internal\Event\TaskSuspendedEvent;

class StateMainSuspendEventListener
{
    public function __construct(private StateMainInterface $stateMain) {}

    public function __invoke(TaskSuspendedEvent $event): void
    {
        $this->stateMain->suspend($event->task);
    }
}
