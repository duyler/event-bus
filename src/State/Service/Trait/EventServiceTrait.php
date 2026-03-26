<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Build\Event;
use Duyler\EventBus\Dto\Event as EventDto;
use Duyler\EventBus\Service\EventService;

/**
 * @property EventService $eventService
 */
trait EventServiceTrait
{
    public function dispatchEvent(EventDto $event, string $scope = 'common'): void
    {
        $this->eventService->dispatch($event, $scope);
    }

    public function registerEvent(Event $event): void
    {
        $this->eventService->addEvent($event);
    }

    public function removeEvent(string $eventId): void
    {
        $this->eventService->removeEvent($eventId);
    }
}
