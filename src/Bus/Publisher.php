<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Collection\CompleteActionCollection;
use Duyler\EventBus\Internal\Event\ActionIsCompleteEvent;
use Duyler\EventBus\Internal\EventDispatcher;

class Publisher
{
    public function __construct(
        private EventDispatcher $eventDispatcher,
        private CompleteActionCollection $completeActionCollection,
    ) {}

    public function publish(Task $task): void
    {
        $completeAction = new CompleteAction(
            action: $task->action,
            result: $task->getResult(),
        );

        $this->completeActionCollection->save($completeAction);
        $this->eventDispatcher->dispatch(new ActionIsCompleteEvent($completeAction));
    }
}
