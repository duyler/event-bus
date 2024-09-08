<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Internal\Event\TaskBeforeRunEvent;

class StateMainBeforeEventListener
{
    public function __construct(private StateMainInterface $stateMain) {}

    public function __invoke(TaskBeforeRunEvent $event): void
    {
        $this->stateMain->before($event->task);
    }
}
