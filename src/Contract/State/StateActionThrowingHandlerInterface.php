<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateActionThrowingService;
use Duyler\EventBus\State\StateHandlerInterface;

interface StateActionThrowingHandlerInterface extends StateHandlerInterface
{
    public function handle(StateActionThrowingService $stateService): void;
}
