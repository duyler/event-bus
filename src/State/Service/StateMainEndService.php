<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service;

use Duyler\ActionBus\Service\LogService;
use Duyler\ActionBus\Service\ResultService;
use Duyler\ActionBus\Service\RollbackService;
use Duyler\ActionBus\State\Service\Trait\LogServiceTrait;
use Duyler\ActionBus\State\Service\Trait\ResultServiceTrait;
use Duyler\ActionBus\State\Service\Trait\RollbackServiceTrait;

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
