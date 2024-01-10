<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\TriggerRelationCollection;
use Duyler\EventBus\Dto\Trigger;
use Duyler\EventBus\Service\SubscriptionService;
use RuntimeException;

class TriggerDispatcher
{
    public function __construct(
        private TriggerRelationCollection $triggerRelationCollection,
        private SubscriptionService $subscriptionService,
        private ActionCollection $actionCollection,
        private Bus $bus,
    ) {}

    // @todo be refactored
    public function dispatch(Trigger $trigger): void
    {
        if ($trigger->data !== null) {
            if ($trigger->contract === null) {
                throw new RuntimeException('Trigger contract will be received');
            }

            if (is_a($trigger->data, $trigger->contract) === false) {
                throw new RuntimeException('Trigger data will be compatible with ' . $trigger->contract);
            }
        } else {
            if ($trigger->contract !== null) {
                throw new RuntimeException('Trigger data will be received for ' . $trigger->contract);
            }
        }

        $subscriptions = $this->subscriptionService->getSubscriptions(
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
