<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service;

use Duyler\ActionBus\Dto\Action;
use Duyler\ActionBus\Formatter\IdFormatter;
use Duyler\ActionBus\Service\ActionService;
use Duyler\ActionBus\Service\SubscriptionService;
use Duyler\ActionBus\Service\TriggerService;
use Duyler\ActionBus\State\Service\Trait\ActionServiceTrait;
use Duyler\ActionBus\State\Service\Trait\SubscriptionServiceTrait;
use Duyler\ActionBus\State\Service\Trait\TriggerServiceTrait;
use UnitEnum;

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

    public function getById(string|UnitEnum $actionId): Action
    {
        return $this->actionService->getById(IdFormatter::format($actionId));
    }
}
