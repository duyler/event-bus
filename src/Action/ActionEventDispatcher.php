<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Dto\Event;
use Duyler\EventBus\Service\EventService;
use InvalidArgumentException;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface;

class ActionEventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private EventService $eventService,
    ) {}

    #[Override]
    public function dispatch(object $event): void
    {
        if (false === $event instanceof Event) {
            throw new InvalidArgumentException('Event must be an instance of ' . Event::class);
        }

        $this->eventService->dispatch($event);
    }
}
