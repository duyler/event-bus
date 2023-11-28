<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\SubscriptionService;
use Duyler\EventBus\State\Service\Trait\ActionService as ActionServiceTrait;
use Duyler\EventBus\State\Service\Trait\SubscriptionService as SubscriptionServiceTrait;

class StateMainStartService
{
    use ActionServiceTrait;
    use SubscriptionServiceTrait;

    public function __construct(
        private readonly ActionService $actionService,
        private readonly SubscriptionService $subscriptionService,
    ) {}
}
