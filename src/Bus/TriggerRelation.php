<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Trigger;

readonly class TriggerRelation
{
    public function __construct(
        public Action $action,
        public Trigger $trigger,
    ) {}
}
