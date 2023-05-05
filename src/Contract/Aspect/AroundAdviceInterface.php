<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\Aspect;

use Duyler\EventBus\Dto\Result;

interface AroundAdviceInterface
{
    public function handle(callable $action): Result;
}
