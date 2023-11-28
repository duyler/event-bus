<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Action\ActionContainer;
use Duyler\EventBus\Bus\Task;
use Duyler\EventBus\Service\ResultService;
use Duyler\EventBus\State\Service\Trait\ResultService as ResultServiceTrait;
use Duyler\EventBus\State\Service\Trait\TaskSuspendService as TaskSuspendServiceTrait;

class StateMainSuspendService
{
    use ResultServiceTrait;
    use TaskSuspendServiceTrait;

    public function __construct(
        private readonly ResultService $resultService,
        private readonly Task $task,
        public readonly ActionContainer $container,
    ) {
    }

    public function getActionId(): string
    {
        return $this->task->action->id;
    }
}
