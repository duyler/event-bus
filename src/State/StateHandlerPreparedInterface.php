<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

interface StateHandlerPreparedInterface
{
    public function prepare(): void;
}
