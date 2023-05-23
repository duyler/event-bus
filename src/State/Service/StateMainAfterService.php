<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Control;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\State\Service\Trait\LogService;
use Duyler\EventBus\State\Service\Trait\ResultService;
use Duyler\EventBus\State\Service\Trait\ActionService;

class StateMainAfterService
{
    use ActionService;
    use ResultService;
    use LogService;

    public function __construct(
        public readonly ResultStatus  $resultStatus,
        public readonly object | null $resultData,
        public readonly string        $actionId,
        private readonly Control      $control,
    ) {
    }
}
