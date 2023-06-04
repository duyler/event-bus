<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\BusService;
use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\SubscriptionCollection;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Enum\ResultStatus;
use InvalidArgumentException;

readonly class SubscriptionService
{
    public function __construct(
        private SubscriptionCollection $subscriptionCollection,
        private ActionCollection       $actionCollection,
        private BusService             $busService,
    ) {
    }

    public function addSubscription(Subscription $subscription): void
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

    public function subscriptionIsExists(Subscription $subscription): bool
    {
        return $this->subscriptionCollection->isExists($subscription);
    }

    public function resolveSubscriptions(string $actionId, ResultStatus $status): void
    {
        $subscriptions = $this->subscriptionCollection->getSubscriptions($actionId, $status);

        foreach ($subscriptions as $subscription) {

            $action = $this->actionCollection->get($subscription->actionId);

            $this->busService->doAction($action);
        }
    }
}
