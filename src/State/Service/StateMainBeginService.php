<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service;

use Duyler\ActionBus\Build\Action;
use Duyler\ActionBus\Formatter\ActionIdFormatter;
use Duyler\ActionBus\Service\ActionService;
use Duyler\ActionBus\Service\SubscriptionService;
use Duyler\ActionBus\Service\EventService;
use Duyler\ActionBus\State\Service\Trait\ActionServiceTrait;
use Duyler\ActionBus\State\Service\Trait\SubscriptionServiceTrait;
use Duyler\ActionBus\State\Service\Trait\EventServiceTrait;
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
        return $this->actionService->getById(ActionIdFormatter::toString($actionId));
    }
}
