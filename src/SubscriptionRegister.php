<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\SubscriptionCollection;
use Duyler\EventBus\Dto\Subscription;
use InvalidArgumentException;

readonly class SubscriptionRegister
{
    public function __construct(
        private SubscriptionCollection $subscriptionCollection,
        private ActionCollection       $actionCollection
    ) {
    }

    public function add(Subscription $subscription): void
    {
        if ($this->actionCollection->isExists($subscription->actionId) === false) {
            throw new InvalidArgumentException(
                'Action ' . $subscription->actionId . ' not registered in the bus'
            );
        }

        if ($this->actionCollection->isExists($subscription->subjectId) === false) {
            throw new InvalidArgumentException(
                'Subscribed action ' . $subscription->subjectId . ' not registered in the bus'
            );
        }

        $this->subscriptionCollection->save($subscription);
    }
}
