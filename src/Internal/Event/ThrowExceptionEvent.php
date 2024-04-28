<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Event;

use Throwable;

readonly class ThrowExceptionEvent
{
    public function __construct(public Throwable $exception) {}
}
