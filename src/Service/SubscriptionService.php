<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Service;

use Duyler\ActionBus\Bus\Bus;
use Duyler\ActionBus\Bus\CompleteAction;
use Duyler\ActionBus\Collection\ActionCollection;
use Duyler\ActionBus\Collection\SubscriptionCollection;
use Duyler\ActionBus\Dto\Subscription;
use Duyler\ActionBus\Exception\SubscribedActionNotDefinedException;
use Duyler\ActionBus\Exception\SubscriptionAlreadyDefinedException;
use Duyler\ActionBus\Exception\SubscriptionNotFoundException;
use Duyler\ActionBus\Exception\SubscriptionOnNotDefinedActionException;
use Duyler\ActionBus\Exception\SubscriptionOnSilentActionException;

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
            $completeAction->result->status,
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
