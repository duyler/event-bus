<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Build\Event;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Formatter\IdFormatter;
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

        $actionId = $action->getId();
        $result = $task->getResult();

        $eventId = $actionId . IdFormatter::DELIMITER . $result->status->value;

        $eventData = ResultStatus::Success === $result->status
            ? $result->data
            : null;

        $eventType = ResultStatus::Success === $result->status
            ? ($action->getTypeCollection() ?? $action->getType())
            : null;

        $actionEvent = new Event(
            id: $action->getExternalId(),
            status: $result->status,
            type: $eventType,
            immutable: $action->isImmutable(),
        );

        $this->eventService->dispatchActionEvent($eventId, $eventData, $actionEvent);
    }
}
