<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

interface StateHandlerObservedInterface
{
    public function observed(): array;
}
