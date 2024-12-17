<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Event;

use Duyler\EventBus\Bus\CompleteAction;

final readonly class ActionIsReadyToRetryEvent
{
    public function __construct(
        public CompleteAction $completeAction,
    ) {}
}
