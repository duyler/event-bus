<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Contract\State;

use Duyler\ActionBus\State\Service\StateActionAfterService;
use Duyler\ActionBus\State\StateContext;

interface ActionAfterStateHandlerInterface extends StateHandlerObservedInterface, StateHandlerInterface
{
    public function handle(StateActionAfterService $stateService, StateContext $context): void;
}
