<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\BusService;
use Duyler\EventBus\Dto\Subscription;

/**
 * @property BusService $busService
 */
trait SubscriptionService
{
    public function addSubscription(Subscription $subscription): void
    {
        $this->busService->addSubscription($subscription);
    }

    public function subscriptionIsExists(Subscription $subscription): bool
    {
        return $this->busService->subscriptionIsExists($subscription);
    }
}
