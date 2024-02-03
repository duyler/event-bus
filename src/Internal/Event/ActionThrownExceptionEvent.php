<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Event;

use Duyler\EventBus\Dto\Action;
use Throwable;

readonly class ActionThrownExceptionEvent
{
    public function __construct(public Action $action, public Throwable $exception) {}
}
