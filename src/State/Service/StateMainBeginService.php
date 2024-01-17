<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\SubscriptionService;
use Duyler\EventBus\Service\TriggerService;
use Duyler\EventBus\State\Service\Trait\ActionServiceTrait;
use Duyler\EventBus\State\Service\Trait\SubscriptionServiceTrait;
use Duyler\EventBus\State\Service\Trait\TriggerServiceTrait;

class StateMainBeginService
{
    use ActionServiceTrait;
    use SubscriptionServiceTrait;
    use TriggerServiceTrait;

    public function __construct(
        private readonly ActionService $actionService,
        private readonly SubscriptionService $subscriptionService,
        private readonly TriggerService $triggerService,
    ) {}

    public function addSharedService(object $service, array $bind = []): void
    {
        $this->actionService->addSharedService($service, $bind);
    }

    public function getById(string $actionId): Action
    {
        return $this->actionService->getById($actionId);
    }

    /** @return array<string, Action> */
    public function getByContract(string $contract): array
    {
        return $this->actionService->getByContract($contract);
    }
}
