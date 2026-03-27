<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

use Duyler\EventBus\Bus\Actor;
use Throwable;

interface StateActorInterface
{
    public function before(Actor $actor, ?object $argument): void;

    public function after(Actor $actor, mixed $resultData): void;

    public function throwing(Actor $actor, Throwable $exception): void;
}
