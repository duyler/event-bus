<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Event;

use Duyler\EventBus\Build\Event;

readonly class EventAddedEvent
{
    public function __construct(
        public Event $event,
    ) {}
}
