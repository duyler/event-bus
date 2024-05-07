<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service;

use Duyler\ActionBus\Bus\ActionContainer;
use Duyler\ActionBus\Bus\Task;
use Duyler\ActionBus\Formatter\ActionIdFormatter;
use Duyler\ActionBus\Service\ActionService;
use Duyler\ActionBus\Service\ResultService;
use Duyler\ActionBus\Service\SubscriptionService;
use Duyler\ActionBus\Service\TriggerService;
use Duyler\ActionBus\State\Service\Trait\ActionServiceTrait;
use Duyler\ActionBus\State\Service\Trait\ResultServiceTrait;
use Duyler\ActionBus\State\Service\Trait\SubscriptionServiceTrait;
use Duyler\ActionBus\State\Service\Trait\TaskSuspendServiceTrait;
use Duyler\ActionBus\State\Service\Trait\TriggerServiceTrait;
use UnitEnum;

class StateMainSuspendService
{
    use ResultServiceTrait;
    use TaskSuspendServiceTrait;
    use ActionServiceTrait;
    use TriggerServiceTrait;
    use SubscriptionServiceTrait;

    public function __construct(
        private readonly ResultService $resultService,
        private readonly Task $task,
        private readonly ActionContainer $container,
        private readonly ActionService $actionService,
        private readonly TriggerService $triggerService,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function getActionId(): string|UnitEnum
    {
        return ActionIdFormatter::reverse($this->task->action->id);
    }

    public function getContainer(): ActionContainer
    {
        return $this->container;
    }
}
