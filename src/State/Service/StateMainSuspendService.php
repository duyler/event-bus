<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Action\ActionContainer;
use Duyler\EventBus\Bus\Task;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\ResultService;
use Duyler\EventBus\Service\TriggerService;
use Duyler\EventBus\State\Service\Trait\ActionServiceTrait;
use Duyler\EventBus\State\Service\Trait\ResultServiceTrait;
use Duyler\EventBus\State\Service\Trait\TaskSuspendServiceTrait;
use Duyler\EventBus\State\Service\Trait\TriggerServiceTrait;

class StateMainSuspendService
{
    use ResultServiceTrait;
    use TaskSuspendServiceTrait;
    use ActionServiceTrait;
    use TriggerServiceTrait;

    public function __construct(
        private readonly ResultService $resultService,
        private readonly Task $task,
        private readonly ActionContainer $container,
        private readonly ActionService $actionService,
        private readonly TriggerService $triggerService,
    ) {}

    public function getActionId(): string
    {
        return $this->task->action->id;
    }

    public function getContainer(): ActionContainer
    {
        return $this->container;
    }
}
