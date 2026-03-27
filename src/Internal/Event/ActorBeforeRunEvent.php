<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Event;

use Duyler\EventBus\Bus\Actor;

readonly class ActorBeforeRunEvent
{
    public function __construct(
        public Actor $actor,
        public ?object $argument = null,
    ) {}
}
