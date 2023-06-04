<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\BusService;
use Duyler\EventBus\State\Service\Trait\LogService;

class StateMainBeforeService
{
    use LogService;

    public function __construct(
        public readonly string      $actionId,
        private readonly BusService $busService,
    ) {
    }
}
