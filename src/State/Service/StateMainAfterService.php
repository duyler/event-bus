<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Service\ActorService;
use Duyler\EventBus\Service\EventService;
use Duyler\EventBus\Service\LogService;
use Duyler\EventBus\Service\ResultService;
use Duyler\EventBus\Service\RollbackService;
use Duyler\EventBus\State\Service\Trait\ActorServiceTrait;
use Duyler\EventBus\State\Service\Trait\EventServiceTrait;
use Duyler\EventBus\State\Service\Trait\LogServiceTrait;
use Duyler\EventBus\State\Service\Trait\ResultServiceTrait;
use Duyler\EventBus\State\Service\Trait\RollbackServiceTrait;
use UnitEnum;

class StateMainAfterService
{
    use ActorServiceTrait;
    use ResultServiceTrait;
    use LogServiceTrait;
    use EventServiceTrait;
    use RollbackServiceTrait;

    public function __construct(
        private readonly ResultStatus $resultStatus,
        private readonly ?object $resultData,
        private readonly string|UnitEnum $actorId,
        private readonly string $scope,
        private readonly ActorService $actorService,
        private readonly ResultService $resultService,
        private readonly LogService $logService,
        private readonly EventService $eventService,
        private readonly RollbackService $rollbackService,
    ) {}

    public function getActorId(): string|UnitEnum
    {
        return $this->actorId;
    }

    public function getResultData(): ?object
    {
        return $this->resultData;
    }

    public function getStatus(): ResultStatus
    {
        return $this->resultStatus;
    }

    public function getScope(): string
    {
        return $this->scope;
    }
}
