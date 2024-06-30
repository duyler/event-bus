<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service;

use Duyler\ActionBus\Service\ActionService;
use Duyler\ActionBus\Service\QueueService;
use Duyler\ActionBus\Service\EventService;
use Duyler\ActionBus\State\Service\Trait\ActionServiceTrait;
use Duyler\ActionBus\State\Service\Trait\QueueServiceTrait;
use Duyler\ActionBus\State\Service\Trait\EventServiceTrait;

class StateMainCyclicService
{
    use QueueServiceTrait;
    use ActionServiceTrait;
    use EventServiceTrait;

    public function __construct(
        private readonly QueueService $queueService,
        private readonly ActionService $actionService,
        private readonly EventService $eventService,
    ) {}
}
