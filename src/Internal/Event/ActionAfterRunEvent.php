<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Event;

use Duyler\EventBus\Bus\Action;

readonly class ActionAfterRunEvent
{
    public function __construct(
        public Action $action,
        public mixed $result = null,
    ) {}
}
