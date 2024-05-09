<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service;

use Duyler\ActionBus\Bus\ActionContainer;
use Duyler\ActionBus\Service\ActionService;
use Duyler\ActionBus\Service\ResultService;
use Duyler\ActionBus\Service\SubscriptionService;
use Duyler\ActionBus\Service\TriggerService;
use Duyler\ActionBus\State\Service\Trait\ActionServiceTrait;
use Duyler\ActionBus\State\Service\Trait\ResultServiceTrait;
use Duyler\ActionBus\State\Service\Trait\SubscriptionServiceTrait;
use Duyler\ActionBus\State\Service\Trait\TaskSuspendResumeServiceTrait;
use Duyler\ActionBus\State\Service\Trait\TriggerServiceTrait;
use Duyler\ActionBus\State\Suspend;

class StateMainResumeService
{
    use ResultServiceTrait;
    use TaskSuspendResumeServiceTrait;
    use ActionServiceTrait;
    use TriggerServiceTrait;
    use SubscriptionServiceTrait;

    public function __construct(
        private readonly Suspend $suspend,
        private readonly ResultService $resultService,
        private readonly ActionContainer $container,
        private readonly ActionService $actionService,
        private readonly TriggerService $triggerService,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function getActionContainer(): ActionContainer
    {
        return $this->container;
    }
}
