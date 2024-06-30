<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service\Trait;

use Duyler\ActionBus\Build\Subscription;
use Duyler\ActionBus\Service\SubscriptionService;

/**
 * @property SubscriptionService $subscriptionService
 */
trait SubscriptionServiceTrait
{
    public function addSubscription(Subscription $subscription): void
    {
        $this->subscriptionService->addSubscription($subscription);
    }

    public function subscriptionIsExists(Subscription $subscription): bool
    {
        return $this->subscriptionService->subscriptionIsExists($subscription);
    }

    public function removeSubscription(Subscription $subscription): void
    {
        $this->subscriptionService->remove($subscription);
    }
}
