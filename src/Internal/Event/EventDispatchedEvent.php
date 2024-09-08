<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Event;

use Duyler\EventBus\Dto\Event;

readonly class EventDispatchedEvent
{
    public function __construct(public Event $event) {}
}
