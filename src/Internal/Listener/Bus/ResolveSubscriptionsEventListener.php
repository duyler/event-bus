<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\Bus;

use Duyler\ActionBus\Storage\CompleteActionStorage;
use Duyler\ActionBus\Internal\Event\TaskAfterRunEvent;
use Duyler\ActionBus\Service\SubscriptionService;

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
