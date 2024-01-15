<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateActionThrowingService;
use Duyler\EventBus\State\StateContext;

interface ActionThrowingStateHandlerInterface extends StateHandlerObservedInterface, StateHandlerInterface
{
    public function handle(StateActionThrowingService $stateService, StateContext $context): void;
}
