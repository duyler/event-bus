<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\SubscriptionService;
use Duyler\EventBus\Service\EventService;
use Duyler\EventBus\State\Service\Trait\ActionServiceTrait;
use Duyler\EventBus\State\Service\Trait\SubscriptionServiceTrait;
use Duyler\EventBus\State\Service\Trait\EventServiceTrait;
use UnitEnum;

class StateMainBeginService
{
    use ActionServiceTrait;
    use SubscriptionServiceTrait;
    use EventServiceTrait;

    public function __construct(
        private readonly ActionService $actionService,
        private readonly SubscriptionService $subscriptionService,
        private readonly EventService $eventService,
    ) {}

    public function getById(string|UnitEnum $actionId): Action
    {
        return $this->actionService->getById(IdFormatter::toString($actionId));
    }
}
