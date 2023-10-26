<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateActionThrowingService;
use Duyler\EventBus\State\StateHandlerInterface;
use Duyler\EventBus\State\StateHandlerObservedInterface;

interface ActionThrowingStateHandlerInterface extends
    StateHandlerObservedInterface,
    StateHandlerInterface
{
    public function handle(StateActionThrowingService $stateService): void;
}
