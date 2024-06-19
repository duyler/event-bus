<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service;

use Duyler\ActionBus\Enum\ResultStatus;
use Duyler\ActionBus\Formatter\ActionIdFormatter;
use Duyler\ActionBus\Service\ActionService;
use Duyler\ActionBus\Service\LogService;
use Duyler\ActionBus\Service\ResultService;
use Duyler\ActionBus\Service\RollbackService;
use Duyler\ActionBus\Service\SubscriptionService;
use Duyler\ActionBus\Service\EventService;
use Duyler\ActionBus\State\Service\Trait\ActionServiceTrait;
use Duyler\ActionBus\State\Service\Trait\LogServiceTrait;
use Duyler\ActionBus\State\Service\Trait\ResultServiceTrait;
use Duyler\ActionBus\State\Service\Trait\RollbackServiceTrait;
use Duyler\ActionBus\State\Service\Trait\SubscriptionServiceTrait;
use Duyler\ActionBus\State\Service\Trait\EventServiceTrait;
use UnitEnum;

class StateMainAfterService
{
    use ActionServiceTrait;
    use ResultServiceTrait;
    use LogServiceTrait;
    use EventServiceTrait;
    use RollbackServiceTrait;
    use SubscriptionServiceTrait;

    public function __construct(
        private readonly ResultStatus $resultStatus,
        private readonly ?object $resultData,
        private readonly string $actionId,
        private readonly ActionService $actionService,
        private readonly ResultService $resultService,
        private readonly LogService $logService,
        private readonly EventService $eventService,
        private readonly RollbackService $rollbackService,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function getActionId(): string|UnitEnum
    {
        return ActionIdFormatter::reverse($this->actionId);
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
