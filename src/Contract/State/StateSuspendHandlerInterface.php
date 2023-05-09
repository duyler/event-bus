<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateSuspendService;
use Duyler\EventBus\State\StateHandlerInterface;

interface StateSuspendHandlerInterface extends StateHandlerInterface
{
    public function handle(StateSuspendService $stateService): void;
}
