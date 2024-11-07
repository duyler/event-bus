<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\State;
use Duyler\EventBus\Internal\Event\EventDispatchedEvent;
use Duyler\EventBus\Service\EventService;

class DispatchEventEventListener
{
    public function __construct(
        private EventService $eventService,
        private State $state,
    ) {}

    public function __invoke(EventDispatchedEvent $event): void
    {
        $this->state->pushEventLog($event->event->id);
        $this->eventService->dispatch($event->event);
    }
}
