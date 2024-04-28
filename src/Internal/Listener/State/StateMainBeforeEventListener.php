<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\State;

use Duyler\ActionBus\Contract\StateMainInterface;
use Duyler\ActionBus\Internal\Event\TaskBeforeRunEvent;

class StateMainBeforeEventListener
{
    public function __construct(private StateMainInterface $stateMain) {}

    public function __invoke(TaskBeforeRunEvent $event): void
    {
        $this->stateMain->before($event->task);
    }
}
