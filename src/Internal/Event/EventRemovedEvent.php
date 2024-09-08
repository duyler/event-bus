<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Event;

readonly class EventRemovedEvent
{
    public function __construct(
        public string $eventId,
    ) {}
}
