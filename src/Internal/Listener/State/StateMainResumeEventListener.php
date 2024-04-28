<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\State;

use Duyler\ActionBus\Contract\StateMainInterface;
use Duyler\ActionBus\Internal\Event\TaskResumeEvent;

class StateMainResumeEventListener
{
    public function __construct(private StateMainInterface $stateMain) {}

    public function __invoke(TaskResumeEvent $event): void
    {
        $this->stateMain->resume($event->task);
    }
}
