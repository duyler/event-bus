<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Event;

use Duyler\ActionBus\Build\Action;
use Throwable;

readonly class ActionThrownExceptionEvent
{
    public function __construct(public Action $action, public Throwable $exception) {}
}
