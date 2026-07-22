<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

use Duyler\EventBus\Bus\Actor;
use Throwable;

interface StateActorInterface
{
    public function before(Actor $actor, ?object $argument, string $scope): void;

    public function after(Actor $actor, mixed $resultData, string $scope): void;

    public function throwing(Actor $actor, Throwable $exception, string $scope): void;
}
