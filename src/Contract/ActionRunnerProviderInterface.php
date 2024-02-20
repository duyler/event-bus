<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

use Closure;
use Duyler\EventBus\Dto\Action;

interface ActionRunnerProviderInterface
{
    public function getRunner(Action $action): Closure;
}
