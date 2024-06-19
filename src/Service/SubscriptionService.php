<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Service;

use Duyler\ActionBus\Build\Subscription;
use Duyler\ActionBus\Bus\Bus;
use Duyler\ActionBus\Bus\CompleteAction;
use Duyler\ActionBus\Exception\SubscribedActionNotDefinedException;
use Duyler\ActionBus\Exception\SubscriptionAlreadyDefinedException;
use Duyler\ActionBus\Exception\SubscriptionNotFoundException;
use Duyler\ActionBus\Exception\SubscriptionOnNotDefinedActionException;
use Duyler\ActionBus\Exception\SubscriptionOnSilentActionException;
use Duyler\ActionBus\Storage\ActionStorage;
use Duyler\ActionBus\Storage\SubscriptionStorage;

readonly class SubscriptionService
{
    public function __construct(
        private SubscriptionStorage $subscriptionStorage,
        private ActionStorage $actionStorage,
        private Bus $bus,
    ) {}

    public function addSubscription(Subscription $subscription): void
    {
        if ($this->subscriptionStorage->isExists($subscription)) {
            throw new SubscriptionAlreadyDefinedException($subscription);
        }

        if (false === $this->actionStorage->isExists($subscription->actionId)) {
            throw new SubscribedActionNotDefinedException($subscription->subjectId);
        }

        if (false === $this->actionStorage->isExists($subscription->subjectId)) {
            throw new SubscriptionOnNotDefinedActionException($subscription);
        }

        $subject = $this->actionStorage->get($subscription->subjectId);

        if ($subject->silent) {
            throw new SubscriptionOnSilentActionException($subscription->actionId, $subscription->subjectId);
        }

        $this->subscriptionStorage->save($subscription);
    }

    public function subscriptionIsExists(Subscription $subscription): bool
    {
        return $this->subscriptionStorage->isExists($subscription);
    }

    public function resolveSubscriptions(CompleteAction $completeAction): void
    {
        $subscriptions = $this->subscriptionStorage->getSubscriptions(
            $completeAction->action->id,
            $completeAction->result->status,
        );

        foreach ($subscriptions as $subscription) {
            $action = $this->actionStorage->get($subscription->actionId);

            $this->bus->doAction($action);
        }
    }

    public function remove(Subscription $subscription): void
    {
        if (false === $this->subscriptionStorage->isExists($subscription)) {
            throw new SubscriptionNotFoundException($subscription);
        }

        $this->subscriptionStorage->remove($subscription);
    }
}
