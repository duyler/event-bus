<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Event;

use Duyler\EventBus\Bus\Actor;
use Throwable;

readonly class ActorThrownExceptionEvent
{
    public function __construct(public Actor $actor, public Throwable $exception) {}
}
