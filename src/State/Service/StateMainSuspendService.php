<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Action\ActionContainer;
use Duyler\EventBus\BusService;
use Duyler\EventBus\State\Service\Trait\ResultService;
use Duyler\EventBus\State\Service\Trait\TaskSuspendService;
use Duyler\EventBus\Task;

class StateMainSuspendService
{
    use ResultService;
    use TaskSuspendService;

    public function __construct(
        private readonly BusService     $busService,
        private readonly Task           $task,
        public readonly ActionContainer $container,
    ) {
    }

    public function getActionId(): string
    {
        return $this->task->action->id;
    }
}
