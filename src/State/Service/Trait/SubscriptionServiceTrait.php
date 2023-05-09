<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Control;
use Duyler\EventBus\Dto\Subscription;

/**
 * @property Control $control
 */
trait SubscriptionServiceTrait
{
    public function addSubscription(Subscription $subscription): void
    {
        $this->control->addSubscription($subscription);
    }

    public function subscriptionIsExists(Subscription $subscription): bool
    {
        return $this->control->subscriptionIsExists($subscription);
    }
}
