<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Control;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\State\Service\Trait\LogServiceTrait;
use Duyler\EventBus\State\Service\Trait\ResultServiceTrait;
use Duyler\EventBus\State\Service\Trait\ActionServiceTrait;
use Duyler\EventBus\State\StateServiceInterface;

class StateAfterService implements StateServiceInterface
{
    use ActionServiceTrait;
    use ResultServiceTrait;
    use LogServiceTrait;

    public function __construct(
        public readonly ResultStatus  $resultStatus,
        public readonly object | null $resultData,
        public readonly string        $actionId,
        private readonly Control      $control,
    ) {
    }
}
