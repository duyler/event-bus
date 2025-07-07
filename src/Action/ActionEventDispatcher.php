<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Dto\Event;
use Duyler\EventBus\Service\EventService;
use Fiber;
use InvalidArgumentException;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class ActionEventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private readonly EventService $eventService,
    ) {}

    /**
     * @return Event
     * @throws Throwable
     */
    #[Override]
    public function dispatch(object $event): object
    {
        if (false === $event instanceof Event) {
            throw new InvalidArgumentException('Event must be an instance of ' . Event::class);
        }

        Fiber::suspend(
            function () use ($event): void {
                $this->eventService->dispatch($event);
            },
        );

        return $event;
    }
}
