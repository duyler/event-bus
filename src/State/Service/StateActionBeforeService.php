<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Bus\ActionContainer;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\SubscriptionService;

class StateActionBeforeService
{
    public function __construct(
        private readonly ActionContainer $container,
        private readonly string $actionId,
        private readonly SubscriptionService $subscriptionService,
        private readonly ActionService $actionService,
    ) {}

    public function getContainer(): ActionContainer
    {
        return $this->container;
    }

    public function getActionId(): string
    {
        return $this->actionId;
    }

    public function removeSubscription(Subscription $subscription): void
    {
        $this->subscriptionService->remove($subscription);
    }

    public function getArgument(): object
    {
        return $this->actionService->getArgument($this->actionId);
    }

    public function argumentIsExists(): bool
    {
        return $this->actionService->argumentIsExists($this->actionId);
    }
}
