<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Bus\Task;
use Duyler\EventBus\Service\ActorService;
use Duyler\EventBus\Service\LogService;
use Duyler\EventBus\Service\QueueService;
use Duyler\EventBus\Service\ResultService;
use Duyler\EventBus\Service\RollbackService;
use Duyler\EventBus\State\Service\Trait\ActorServiceTrait;
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
    use ActorServiceTrait;
    use QueueServiceTrait;

    public function __construct(
        private readonly ResultService $resultService,
        private readonly LogService $logService,
        private readonly RollbackService $rollbackService,
        private readonly ActorService $actorService,
        private readonly QueueService $queueService,
        private readonly Task $task,
    ) {}

    public function getActorId(): string|UnitEnum
    {
        return $this->task->actor->getExternalId();
    }
}
