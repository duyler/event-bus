<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Internal\Event\TaskQueueIsEmptyEvent;

class StateMainEmptyListener
{
    public function __construct(private readonly StateMainInterface $stateMain) {}

    public function __invoke(TaskQueueIsEmptyEvent $event): void
    {
        $this->stateMain->empty();
    }
}
