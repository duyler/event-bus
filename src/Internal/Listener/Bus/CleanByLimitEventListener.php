<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Service\ActorService;
use Duyler\EventBus\Service\EventService;
use Duyler\EventBus\Storage\CompleteActorStorage;
use Duyler\EventBus\Storage\EventRelationStorage;
use Duyler\EventBus\Storage\EventStorage;

use function array_key_first;
use function count;

final readonly class CleanByLimitEventListener
{
    public function __construct(
        private CompleteActorStorage $completeActorStorage,
        private BusConfig $busConfig,
        private ActorService $actorService,
        private EventRelationStorage $eventRelationStorage,
        private EventStorage $eventStorage,
        private EventService $eventService,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        $this->cleanUpActors();
        $this->cleanUpEvents();
    }

    private function cleanUpActors(): void
    {
        if (0 === $this->busConfig->maxCountCompleteActors) {
            return;
        }

        $completeActors = $this->completeActorStorage->getAll();

        if (count($completeActors) > $this->busConfig->maxCountCompleteActors) {
            /** @var string $firstCompleteActorId */
            $firstCompleteActorId = array_key_first($completeActors);
            $firstCompleteActor = $completeActors[$firstCompleteActorId];
            $this->actorService->removeActor($firstCompleteActor->actor->getId());
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
