<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\SubscriptionCollection;
use Duyler\EventBus\Collection\TriggerRelationCollection;
use Duyler\EventBus\Dto\Trigger;
use InvalidArgumentException;

class TriggerDispatcher
{
    public function __construct(
        private TriggerRelationCollection $triggerRelationCollection,
        private SubscriptionCollection $subscriptionCollection,
        private ActionCollection $actionCollection,
        private Bus $bus,
    ) {}

    public function dispatch(Trigger $trigger): void
    {
        if ($this->actionCollection->isExists($trigger->id)) {
            throw new InvalidArgumentException('Trigger id must not match with any action id');
        }

        if ($trigger->data !== null) {
            if ($trigger->contract === null) {
                throw new InvalidArgumentException('Trigger contract will be received');
            }

            if ($trigger->data instanceof $trigger->contract === false) {
                throw new InvalidArgumentException('Trigger data will be compatible with ' . $trigger->contract);
            }
        } else {
            if ($trigger->contract !== null) {
                throw new InvalidArgumentException('Trigger data will be received for ' . $trigger->contract);
            }
        }

        $subscriptions = $this->subscriptionCollection->getSubscriptions(
            $trigger->id,
            $trigger->status
        );

        foreach ($subscriptions as $subscription) {
            $action = $this->actionCollection->get($subscription->actionId);
            $this->triggerRelationCollection->save(new TriggerRelation($subscription, $trigger));
            $this->bus->doAction($action);
        }
    }
}
