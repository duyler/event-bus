<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Service\EventService;

final readonly class DispatchActionEventEventListener
{
    public function __construct(
        private EventService $eventService,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        $task = $event->task;
        $action = $task->action;

        if ($action->isSilent()) {
            return;
        }

        if ($action->isPrivate()) {
            return;
        }

        $actionId = $action->getId();
        $result = $task->getResult();

        $this->eventService->dispatchActionEvent($actionId, $task->getScope(), $result);
    }
}
