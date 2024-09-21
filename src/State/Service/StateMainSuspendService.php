<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Bus\ActionContainer;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\ResultService;
use Duyler\EventBus\Service\TriggerService;
use Duyler\EventBus\Service\EventService;
use Duyler\EventBus\State\Service\Trait\ActionServiceTrait;
use Duyler\EventBus\State\Service\Trait\ResultServiceTrait;
use Duyler\EventBus\State\Service\Trait\TriggerServiceTrait;
use Duyler\EventBus\State\Service\Trait\TaskSuspendResumeServiceTrait;
use Duyler\EventBus\State\Service\Trait\EventServiceTrait;
use Duyler\EventBus\State\Suspend;

class StateMainSuspendService
{
    use ResultServiceTrait;
    use TaskSuspendResumeServiceTrait;
    use ActionServiceTrait;
    use EventServiceTrait;
    use TriggerServiceTrait;

    public function __construct(
        private readonly Suspend $suspend,
        private readonly ResultService $resultService,
        private readonly ActionContainer $container,
        private readonly ActionService $actionService,
        private readonly EventService $eventService,
        private readonly TriggerService $triggerService,
    ) {}

    public function getActionContainer(): ActionContainer
    {
        return $this->container;
    }
}
