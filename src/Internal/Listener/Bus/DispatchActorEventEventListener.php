<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Service\EventService;

final readonly class DispatchActorEventEventListener
{
    public function __construct(
        private EventService $eventService,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        $task = $event->task;
        $actor = $task->actor;

        if ($actor->isSilent()) {
            return;
        }

        if ($actor->isPrivate()) {
            return;
        }

        $actorId = $actor->getId();
        $result = $task->getResult();

        $this->eventService->dispatchActorEvent($actorId, $task->getScope(), $result);
    }
}
