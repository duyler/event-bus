<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Dto\Event;

final readonly class EventRelation
{
    public function __construct(
        public Action $action,
        public Event $event,
    ) {}
}
