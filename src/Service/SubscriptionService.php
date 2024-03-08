<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Bus\Bus;
use Duyler\EventBus\Bus\CompleteAction;
use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\SubscriptionCollection;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Exception\SubscribedActionNotDefinedException;
use Duyler\EventBus\Exception\SubscriptionAlreadyDefinedException;
use Duyler\EventBus\Exception\SubscriptionNotFoundException;
use Duyler\EventBus\Exception\SubscriptionOnNotDefinedActionException;
use Duyler\EventBus\Exception\SubscriptionOnSilentActionException;

readonly class SubscriptionService
{
    public function __construct(
        private SubscriptionCollection $subscriptionCollection,
        private ActionCollection $actionCollection,
        private Bus $bus,
    ) {}

    public function addSubscription(Subscription $subscription): void
    {
        if ($this->subscriptionCollection->isExists($subscription)) {
            throw new SubscriptionAlreadyDefinedException($subscription);
        }

        if (false === $this->actionCollection->isExists($subscription->actionId)) {
            throw new SubscribedActionNotDefinedException($subscription->subjectId);
        }

        if (false === $this->actionCollection->isExists($subscription->subjectId)) {
            throw new SubscriptionOnNotDefinedActionException($subscription);
        }

        $subject = $this->actionCollection->get($subscription->subjectId);

        if ($subject->silent) {
            throw new SubscriptionOnSilentActionException($subscription->actionId, $subscription->subjectId);
        }

        $this->subscriptionCollection->save($subscription);
    }

    public function subscriptionIsExists(Subscription $subscription): bool
    {
        return $this->subscriptionCollection->isExists($subscription);
    }

    public function resolveSubscriptions(CompleteAction $completeAction): void
    {
        $subscriptions = $this->subscriptionCollection->getSubscriptions(
            $completeAction->action->id,
            $completeAction->result->status
        );

        foreach ($subscriptions as $subscription) {
            $action = $this->actionCollection->get($subscription->actionId);

            $this->bus->doAction($action);
        }
    }

    public function remove(Subscription $subscription): void
    {
        if (false === $this->subscriptionCollection->isExists($subscription)) {
            throw new SubscriptionNotFoundException($subscription);
        }

        $this->subscriptionCollection->remove($subscription);
    }
}
