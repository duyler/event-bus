<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Event;

use Duyler\EventBus\Build\Actor;

readonly class ActorAddedEvent
{
    public function __construct(
        public Actor $actor,
    ) {}
}
