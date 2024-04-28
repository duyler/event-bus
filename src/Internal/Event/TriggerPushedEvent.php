<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Event;

use Duyler\ActionBus\Dto\Trigger;

readonly class TriggerPushedEvent
{
    public function __construct(public Trigger $trigger) {}
}
