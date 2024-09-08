<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\StateContext;

interface StateHandlerObservedInterface
{
    public function observed(StateContext $context): array;
}
