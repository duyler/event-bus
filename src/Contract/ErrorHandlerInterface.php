<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

use Duyler\EventBus\Dto\Log;
use Throwable;

interface ErrorHandlerInterface
{
    public function handle(Throwable $exception, ?Log $log = null): void;
}
