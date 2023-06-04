<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\BusService;
use Duyler\EventBus\Dto\Subscription;

/**
 * @property \Duyler\EventBus\Service\SubscriptionService $subscriptionService
 */
trait SubscriptionService
{
    public function addSubscription(Subscription $subscription): void
    {
        $this->subscriptionService->addSubscription($subscription);
    }

    public function subscriptionIsExists(Subscription $subscription): bool
    {
        return $this->subscriptionService->subscriptionIsExists($subscription);
    }
}
