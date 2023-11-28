<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Collection\EventCollection;
use Duyler\EventBus\Exception\CircularCallActionException;
use Duyler\EventBus\Exception\ConsecutiveRepeatedActionException;

readonly class Publisher
{
    public function __construct(
        private EventDispatcher $eventDispatcher,
        private EventCollection $eventCollection,
    ) {}

    /**
     * @throws ConsecutiveRepeatedActionException
     * @throws CircularCallActionException
     */
    public function publish(Task $task): void
    {
        $event = new Event(
            action: $task->action,
            result: $task->getResult(),
        );

        $this->eventCollection->save($event);
        $this->eventDispatcher->dispatch($event);
    }
}
