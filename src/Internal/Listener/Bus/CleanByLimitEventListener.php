<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\EventService;
use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\EventBus\Storage\EventRelationStorage;
use Duyler\EventBus\Storage\EventStorage;

final readonly class CleanByLimitEventListener
{
    public function __construct(
        private CompleteActionStorage $completeActionStorage,
        private BusConfig $busConfig,
        private ActionService $actionService,
        private EventRelationStorage $eventRelationStorage,
        private EventStorage $eventStorage,
        private EventService $eventService,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        $this->cleanUpActions();
        $this->cleanUpEvents();
    }

    private function cleanUpActions(): void
    {
        if (0 === $this->busConfig->maxCountCompleteActions) {
            return;
        }

        $completeActions = $this->completeActionStorage->getAll();

        if (count($completeActions) > $this->busConfig->maxCountCompleteActions) {
            /** @var string $firstCompleteActionId */
            $firstCompleteActionId = array_key_first($completeActions);
            $firstCompleteAction = $completeActions[$firstCompleteActionId];
            $this->actionService->removeAction($firstCompleteAction->action->id);
            $this->completeActionStorage->remove($firstCompleteAction->action->id);
            $this->eventRelationStorage->removeByActionId($firstCompleteAction->action->id);
        }
    }

    private function cleanUpEvents(): void
    {
        if (0 === $this->busConfig->maxCountEvents) {
            return;
        }

        $events = $this->eventStorage->getAllDynamic();

        if (count($events) > $this->busConfig->maxCountEvents) {
            /** @var string $firstEventId */
            $firstEventId = array_key_first($events);
            if ($this->eventRelationStorage->isExists($firstEventId)) {
                $this->eventService->removeEvent($firstEventId);
            }
        }
    }
}
