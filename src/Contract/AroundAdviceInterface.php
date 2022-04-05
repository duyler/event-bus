<?php

declare(strict_types=1);

namespace Konveyer\EventBus\Contract;

use Konveyer\EventBus\DTO\Result;

interface AroundAdviceInterface
{
    public function handle(callable $action): Result;
}
