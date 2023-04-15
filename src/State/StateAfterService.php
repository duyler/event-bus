<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Control;
use Duyler\EventBus\Enum\ResultStatus;

class StateAfterService extends AbstractStateService implements StateServiceInterface
{
    use StateServiceTrait;

    public function __construct(
        public readonly ResultStatus  $resultStatus,
        public readonly object | null $resultData,
        public readonly string        $actionId,
        Control      $control
    ) {
        parent::__construct($control);
    }
}
