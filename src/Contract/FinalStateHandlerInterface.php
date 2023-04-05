<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

use Duyler\EventBus\BusControlService;

interface FinalStateHandlerInterface
{
    public function handle(BusControlService $busControlService): void;
}
