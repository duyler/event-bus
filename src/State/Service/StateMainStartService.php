<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\SubscriptionService;
use Duyler\EventBus\Service\TriggerService;
use Duyler\EventBus\State\Service\Trait\ActionService as ActionServiceTrait;
use Duyler\EventBus\State\Service\Trait\SubscriptionService as SubscriptionServiceTrait;
use Duyler\EventBus\State\Service\Trait\TriggerService as TriggerServiceTrait;

class StateMainStartService
{
    use ActionServiceTrait;
    use SubscriptionServiceTrait;
    use TriggerServiceTrait;

    public function __construct(
        private readonly ActionService $actionService,
        private readonly SubscriptionService $subscriptionService,
        private readonly TriggerService $triggerService,
    ) {}
}
