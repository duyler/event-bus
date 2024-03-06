<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Bus\ActionContainer;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Service\SubscriptionService;

class StateActionAfterService
{
    public function __construct(
        private readonly ActionContainer $container,
        private readonly string $actionId,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function getContainer(): ActionContainer
    {
        return $this->container;
    }

    public function getActionId(): string
    {
        return $this->actionId;
    }

    public function removeSubscription(Subscription $subscription): void
    {
        $this->subscriptionService->remove($subscription);
    }
}
