<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\LogService;
use Duyler\EventBus\Service\ResultService;
use Duyler\EventBus\State\Service\Trait\LogService as LogServiceTrait;
use Duyler\EventBus\State\Service\Trait\ResultService as ResultServiceTrait;
use Duyler\EventBus\State\Service\Trait\ActionService as ActionServiceTrait;

class StateMainAfterService
{
    use ActionServiceTrait;
    use ResultServiceTrait;
    use LogServiceTrait;

    public function __construct(
        public readonly ResultStatus $resultStatus,
        public readonly object | null $resultData,
        public readonly string $actionId,
        private readonly ActionService $actionService,
        private readonly ResultService $resultService,
        private readonly LogService $logService,
    ) {
    }
}
