<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Contract;

use Duyler\ActionBus\Build\Action;
use Throwable;

interface StateActionInterface
{
    public function before(Action $action, object|null $argument): void;

    public function after(Action $action, mixed $resultData): void;

    public function throwing(Action $action, Throwable $exception): void;
}
