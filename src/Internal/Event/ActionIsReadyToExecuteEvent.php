<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Event;

use Duyler\EventBus\Build\Action;

final readonly class ActionIsReadyToExecuteEvent
{
    public function __construct(public Action $action) {}
}
