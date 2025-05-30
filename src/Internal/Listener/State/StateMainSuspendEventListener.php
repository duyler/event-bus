<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Bus\State;
use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Internal\Event\TaskSuspendedEvent;

class StateMainSuspendEventListener
{
    public function __construct(
        private readonly StateMainInterface $stateMain,
        private readonly State $state,
    ) {}

    public function __invoke(TaskSuspendedEvent $event): void
    {
        $this->state->pushSuspendedLog($event->task->action->id);
        $this->stateMain->suspend($event->task);
    }
}
