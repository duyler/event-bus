<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\CompleteAction;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Storage\CompleteActionStorage;

final class SaveCompleteActionEventListener
{
    public function __construct(
        private readonly CompleteActionStorage $completeActionStorage,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        $completeAction = new CompleteAction(
            action: $event->task->action,
            result: $event->task->getResult(),
            taskId: $event->task->getId(),
        );

        $this->completeActionStorage->save($completeAction);
    }
}
