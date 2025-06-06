<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

use Duyler\EventBus\Bus\Action;

interface ActionRunnerProviderInterface
{
    public function getRunner(Action $action): ActionRunnerInterface;
}
