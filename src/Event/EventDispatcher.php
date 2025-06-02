<?php

declare(strict_types=1);

namespace Duyler\EventBus\Event;

use Duyler\EventBus\Dto\Event;
use Duyler\EventBus\Service\EventService;
use Fiber;

final class EventDispatcher
{
    private static EventService $eventService;

    public function __construct(
        EventService $eventService,
    ) {
        self::$eventService = $eventService;
    }

    public static function dispatch(Event $event): void
    {
        Fiber::suspend(
            function () use ($event): void {
                self::$eventService->dispatch($event);
            },
        );
    }
}
