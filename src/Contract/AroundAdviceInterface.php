<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

use Duyler\EventBus\Dto\Result;

interface AroundAdviceInterface
{
    public function handle(callable $action): Result;
}
