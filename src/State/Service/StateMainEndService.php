<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Service\LogService;
use Duyler\EventBus\Service\ResultService;
use Duyler\EventBus\Service\RollbackService;
use Duyler\EventBus\State\Service\Trait\LogServiceTrait;
use Duyler\EventBus\State\Service\Trait\ResultServiceTrait;
use Duyler\EventBus\State\Service\Trait\RollbackServiceTrait;

class StateMainEndService
{
    use ResultServiceTrait;
    use LogServiceTrait;
    use RollbackServiceTrait;

    public function __construct(
        private readonly ResultService $resultService,
        private readonly LogService $logService,
        private readonly RollbackService $rollbackService,
    ) {}
}
