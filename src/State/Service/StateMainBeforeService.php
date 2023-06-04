<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Service\LogService;
use Duyler\EventBus\State\Service\Trait\LogService as LogServiceTrait;

class StateMainBeforeService
{
    use LogServiceTrait;

    public function __construct(
        public readonly string      $actionId,
        private readonly LogService $logService,
    ) {
    }
}
