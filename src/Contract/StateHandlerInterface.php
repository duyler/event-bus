<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

use Duyler\EventBus\BusControlService;

interface StateHandlerInterface
{
    public function handle(BusControlService $busControlService): void;
}
