<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Internal\Event\TaskResumeEvent;

class StateMainResumeEventListener
{
    public function __construct(private StateMainInterface $stateMain) {}

    public function __invoke(TaskResumeEvent $event): void
    {
        $this->stateMain->resume($event->task);
    }
}
