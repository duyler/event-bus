<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Action\ActionContainer;
use Duyler\EventBus\Enum\ResultStatus;

readonly class StateActionAfterService
{
    public function __construct(
        public ActionContainer $container,
        public string $actionId,
    ) {
    }
}
