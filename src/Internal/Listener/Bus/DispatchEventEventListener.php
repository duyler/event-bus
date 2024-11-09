<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Internal\Event\EventDispatchedEvent;
use Duyler\EventBus\Service\EventService;

class DispatchEventEventListener
{
    public function __construct(
        private EventService $eventService,
    ) {}

    public function __invoke(EventDispatchedEvent $event): void
    {
        $this->eventService->dispatch($event->event);
    }
}
