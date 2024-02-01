<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Event;

use Duyler\EventBus\Dto\Action;

readonly class ActionBeforeRunEvent
{
    public function __construct(public Action $action) {}
}
