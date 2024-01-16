<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateActionAfterService;
use Duyler\EventBus\State\StateContext;

interface ActionAfterStateHandlerInterface extends StateHandlerObservedInterface, StateHandlerInterface
{
    public function handle(StateActionAfterService $stateService, StateContext $context): void;
}
