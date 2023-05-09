<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateFinalService;

interface StateFinalHandlerInterface
{
    public function handle(StateFinalService $stateService): void;
}
