<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\StateContext;

interface MainAfterStateHandlerInterface extends StateHandlerObservedInterface, StateHandlerInterface
{
    public function handle(StateMainAfterService $stateService, StateContext $context): void;
}
