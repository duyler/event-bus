<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Bus\ActorContainer;
use Duyler\EventBus\Service\ActorService;
use Duyler\EventBus\Service\EventService;
use Duyler\EventBus\Service\ResultService;
use Duyler\EventBus\State\Service\Trait\ActorServiceTrait;
use Duyler\EventBus\State\Service\Trait\EventServiceTrait;
use Duyler\EventBus\State\Service\Trait\ResultServiceTrait;
use Duyler\EventBus\State\Service\Trait\TaskSuspendResumeServiceTrait;
use Duyler\EventBus\State\Suspend;

class StateMainSuspendService
{
    use ResultServiceTrait;
    use TaskSuspendResumeServiceTrait;
    use ActorServiceTrait;
    use EventServiceTrait;

    public function __construct(
        private readonly Suspend $suspend,
        private readonly ResultService $resultService,
        private readonly ActorContainer $container,
        private readonly ActorService $actorService,
        private readonly EventService $eventService,
    ) {}

    public function getActorContainer(): ActorContainer
    {
        return $this->container;
    }
}
