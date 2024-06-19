<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Event;

use Duyler\ActionBus\Build\Action;

readonly class ActionBeforeRunEvent
{
    public function __construct(
        public Action $action,
        public object|null $argument = null,
    ) {}
}
