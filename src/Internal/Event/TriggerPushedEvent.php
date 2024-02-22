<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Event;

use Duyler\EventBus\Dto\Trigger;

readonly class TriggerPushedEvent
{
    public function __construct(public Trigger $trigger) {}
}
