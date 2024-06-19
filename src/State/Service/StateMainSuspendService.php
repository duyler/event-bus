<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service;

use Duyler\ActionBus\Bus\ActionContainer;
use Duyler\ActionBus\Service\ActionService;
use Duyler\ActionBus\Service\ResultService;
use Duyler\ActionBus\Service\SubscriptionService;
use Duyler\ActionBus\Service\EventService;
use Duyler\ActionBus\State\Service\Trait\ActionServiceTrait;
use Duyler\ActionBus\State\Service\Trait\ResultServiceTrait;
use Duyler\ActionBus\State\Service\Trait\SubscriptionServiceTrait;
use Duyler\ActionBus\State\Service\Trait\TaskSuspendResumeServiceTrait;
use Duyler\ActionBus\State\Service\Trait\EventServiceTrait;
use Duyler\ActionBus\State\Suspend;

class StateMainSuspendService
{
    use ResultServiceTrait;
    use TaskSuspendResumeServiceTrait;
    use ActionServiceTrait;
    use EventServiceTrait;
    use SubscriptionServiceTrait;

    public function __construct(
        private readonly Suspend $suspend,
        private readonly ResultService $resultService,
        private readonly ActionContainer $container,
        private readonly ActionService $actionService,
        private readonly EventService $eventService,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function getActionContainer(): ActionContainer
    {
        return $this->container;
    }
}
