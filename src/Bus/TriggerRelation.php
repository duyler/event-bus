<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Dto\Trigger;

readonly class TriggerRelation
{
    public function __construct(
        public Subscription $subscription,
        public Trigger $trigger,
    ) {}
}
