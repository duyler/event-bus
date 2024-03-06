<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\Bus;
use Duyler\EventBus\Bus\CompleteAction;
use Duyler\EventBus\Collection\CompleteActionCollection;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;

class CompleteActionEventListener
{
    public function __construct(
        private CompleteActionCollection $completeActionCollection,
        private Bus $bus,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        $completeAction = new CompleteAction(
            action: $event->task->action,
            result: $event->task->getResult(),
        );

        $this->completeActionCollection->save($completeAction);
        $this->bus->completeDoAction($completeAction);
    }
}
