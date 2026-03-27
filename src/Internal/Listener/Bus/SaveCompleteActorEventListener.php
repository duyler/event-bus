<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\CompleteActor;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Storage\CompleteActorStorage;

final readonly class SaveCompleteActorEventListener
{
    public function __construct(
        private CompleteActorStorage $completeActorStorage,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        $completeActor = new CompleteActor(
            actor: $event->task->actor,
            result: $event->task->getResult(),
            taskId: $event->task->getId(),
            scope: $event->task->getScope(),
        );

        $this->completeActorStorage->save($completeActor);
    }
}
