<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Bus;

use Duyler\ActionBus\Dto\Action;
use Duyler\ActionBus\Dto\Trigger;

final readonly class TriggerRelation
{
    public function __construct(
        public Action $action,
        public Trigger $trigger,
    ) {}
}
