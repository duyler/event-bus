<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Event;

use Duyler\EventBus\Build\Trigger;

class TriggerAddedEvent
{
    public function __construct(
        public Trigger $trigger,
    ) {}
}
