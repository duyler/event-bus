<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Contract\State;

use Duyler\ActionBus\State\StateContext;

interface StateHandlerObservedInterface
{
    public function observed(StateContext $context): array;
}
