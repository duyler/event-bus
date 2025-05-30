<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;

class StateMainAfterEventListener
{
    public function __construct(private readonly StateMainInterface $stateMain) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        $this->stateMain->after($event->task);
    }
}
