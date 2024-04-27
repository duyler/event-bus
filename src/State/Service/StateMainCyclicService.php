<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\QueueService;
use Duyler\EventBus\Service\TriggerService;
use Duyler\EventBus\State\Service\Trait\ActionServiceTrait;
use Duyler\EventBus\State\Service\Trait\QueueServiceTrait;
use Duyler\EventBus\State\Service\Trait\TriggerServiceTrait;

class StateMainCyclicService
{
    use QueueServiceTrait;
    use ActionServiceTrait;
    use TriggerServiceTrait;

    public function __construct(
        private readonly QueueService $queueService,
        private readonly ActionService $actionService,
        private readonly TriggerService $triggerService,
    ) {}
}
