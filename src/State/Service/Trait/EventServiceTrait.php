<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service\Trait;

use Duyler\ActionBus\Build\Event;
use Duyler\ActionBus\Dto\Event as EventDto;
use Duyler\ActionBus\Service\EventService;

/**
 * @property EventService $eventService
 */
trait EventServiceTrait
{
    public function dispatchEvent(EventDto $event): void
    {
        $this->eventService->dispatch($event);
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
