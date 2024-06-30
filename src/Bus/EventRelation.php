<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Bus;

use Duyler\ActionBus\Build\Action;
use Duyler\ActionBus\Dto\Event;

final readonly class EventRelation
{
    public function __construct(
        public Action $action,
        public Event $event,
    ) {}
}
