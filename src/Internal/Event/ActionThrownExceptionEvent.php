<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Event;

use Duyler\EventBus\Build\Action;
use Throwable;

readonly class ActionThrownExceptionEvent
{
    public function __construct(public Action $action, public Throwable $exception) {}
}
