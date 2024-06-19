<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\Bus;

use Duyler\ActionBus\Bus\Log;
use Duyler\ActionBus\Internal\Event\EventDispatchedEvent;
use Duyler\ActionBus\Service\EventService;

class DispatchEventEventListener
{
    public function __construct(
        private EventService $eventService,
        private Log $log,
    ) {}

    public function __invoke(EventDispatchedEvent $event): void
    {
        $this->log->dispatchEventLog($event->event->id);
        $this->eventService->dispatch($event->event);
    }
}
