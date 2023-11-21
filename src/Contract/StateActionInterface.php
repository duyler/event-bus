<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

use Duyler\EventBus\Dto\Action;
use Throwable;

interface StateActionInterface
{
    public function before(Action $action): void;
    public function after(Action $action): void;
    public function throwing(Action $action, Throwable $exception): void;
}
