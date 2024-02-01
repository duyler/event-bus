<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Event;

use Duyler\EventBus\Bus\CompleteAction;

readonly class ActionIsCompleteEvent
{
    public function __construct(
        public CompleteAction $completeAction,
    ) {}
}
