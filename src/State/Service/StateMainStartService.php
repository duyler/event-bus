<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\BusService;
use Duyler\EventBus\State\Service\Trait\ActionService;
use Duyler\EventBus\State\Service\Trait\SubscriptionService;

class StateMainStartService
{
    use ActionService;
    use SubscriptionService;

    public function __construct(
        private readonly BusService $busService,
    ) {
    }
}
