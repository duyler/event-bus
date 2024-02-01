<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Internal\Event\ActionIsCompleteEvent;
use Duyler\EventBus\Service\SubscriptionService;

class ResolveCompleteActionSubscriptionsEventListener
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    public function __invoke(ActionIsCompleteEvent $event): void
    {
        $this->subscriptionService->resolveSubscriptions($event->completeAction);
    }
}
