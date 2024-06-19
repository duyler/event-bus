<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Event;

use Duyler\ActionBus\Dto\Event;

readonly class EventDispatchedEvent
{
    public function __construct(public Event $event) {}
}
