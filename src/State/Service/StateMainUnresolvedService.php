<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Bus\Task;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\LogService;
use Duyler\EventBus\Service\QueueService;
use Duyler\EventBus\Service\ResultService;
use Duyler\EventBus\Service\RollbackService;
use Duyler\EventBus\State\Service\Trait\ActionServiceTrait;
use Duyler\EventBus\State\Service\Trait\LogServiceTrait;
use Duyler\EventBus\State\Service\Trait\QueueServiceTrait;
use Duyler\EventBus\State\Service\Trait\ResultServiceTrait;
use Duyler\EventBus\State\Service\Trait\RollbackServiceTrait;
use UnitEnum;

class StateMainUnresolvedService
{
    use ResultServiceTrait;
    use LogServiceTrait;
    use RollbackServiceTrait;
    use ActionServiceTrait;
    use QueueServiceTrait;

    public function __construct(
        private readonly ResultService $resultService,
        private readonly LogService $logService,
        private readonly RollbackService $rollbackService,
        private readonly ActionService $actionService,
        private readonly QueueService $queueService,
        private readonly Task $task,
    ) {}

    public function getActionId(): string|UnitEnum
    {
        return $this->task->action->externalId;
    }
}
