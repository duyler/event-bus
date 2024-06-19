<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service\Trait;

use Duyler\ActionBus\Dto\Event;
use Duyler\ActionBus\Service\EventService;

/**
 * @property EventService $eventService
 */
trait EventServiceTrait
{
    public function dispatchEvent(Event $event): void
    {
        $this->eventService->dispatch($event);
    }
}
