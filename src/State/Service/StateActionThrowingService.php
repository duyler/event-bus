<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Action\ActionContainer;
use Throwable;

readonly class StateActionThrowingService
{
    public function __construct(
        public ActionContainer $container,
        public Throwable       $exception,
        public string          $actionId,
    ) {
    }
}
