<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

interface StateHandlerInterface
{
    public function observed(): array;
}
