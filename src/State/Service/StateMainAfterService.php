<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\LogService;
use Duyler\EventBus\Service\ResultService;
use Duyler\EventBus\Service\RollbackService;
use Duyler\EventBus\Service\TriggerService;
use Duyler\EventBus\State\Service\Trait\ActionServiceTrait;
use Duyler\EventBus\State\Service\Trait\LogServiceTrait;
use Duyler\EventBus\State\Service\Trait\ResultServiceTrait;
use Duyler\EventBus\State\Service\Trait\RollbackServiceTrait;
use Duyler\EventBus\State\Service\Trait\TriggerServiceTrait;

class StateMainAfterService
{
    use ActionServiceTrait;
    use ResultServiceTrait;
    use LogServiceTrait;
    use TriggerServiceTrait;
    use RollbackServiceTrait;

    public function __construct(
        private readonly ResultStatus $resultStatus,
        private readonly ?object $resultData,
        private readonly string $actionId,
        private readonly ActionService $actionService,
        private readonly ResultService $resultService,
        private readonly LogService $logService,
        private readonly TriggerService $triggerService,
        private readonly RollbackService $rollbackService,
    ) {}

    public function getActionId(): string
    {
        return $this->actionId;
    }

    public function getResultData(): ?object
    {
        return $this->resultData;
    }

    public function getStatus(): ResultStatus
    {
        return $this->resultStatus;
    }
}
