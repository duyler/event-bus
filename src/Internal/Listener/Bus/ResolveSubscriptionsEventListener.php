<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Collection\CompleteActionCollection;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Service\SubscriptionService;

class ResolveSubscriptionsEventListener
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private CompleteActionCollection $completeActionCollection,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        $completeAction = $this->completeActionCollection->get($event->task->action->id);
        $this->subscriptionService->resolveSubscriptions($completeAction);
    }
}
