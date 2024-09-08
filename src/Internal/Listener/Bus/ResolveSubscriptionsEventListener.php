<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Service\SubscriptionService;

class ResolveSubscriptionsEventListener
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private CompleteActionStorage $completeActionStorage,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        $completeAction = $this->completeActionStorage->get($event->task->action->id);
        $this->subscriptionService->resolveSubscriptions($completeAction);
    }
}
