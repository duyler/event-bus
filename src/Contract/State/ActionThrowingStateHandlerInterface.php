<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Contract\State;

use Duyler\ActionBus\State\Service\StateActionThrowingService;
use Duyler\ActionBus\State\StateContext;

interface ActionThrowingStateHandlerInterface extends StateHandlerObservedInterface, StateHandlerInterface
{
    public function handle(StateActionThrowingService $stateService, StateContext $context): void;
}
