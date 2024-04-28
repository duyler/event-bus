<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Contract\State;

use Duyler\ActionBus\State\Service\StateMainAfterService;
use Duyler\ActionBus\State\StateContext;

interface MainAfterStateHandlerInterface extends StateHandlerObservedInterface, StateHandlerInterface
{
    public function handle(StateMainAfterService $stateService, StateContext $context): void;
}
