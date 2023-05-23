<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Control;
use Duyler\EventBus\State\Service\Trait\LogService;
use Duyler\EventBus\State\Service\Trait\ResultService;
use Duyler\EventBus\State\Service\Trait\RollbackService;

class StateMainFinalService
{
    use ResultService;
    use LogService;
    use RollbackService;

    public function __construct(
        private readonly Control $control,
    ) {
    }
}
