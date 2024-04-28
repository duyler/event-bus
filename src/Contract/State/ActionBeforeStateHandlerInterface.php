<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Contract\State;

use Duyler\ActionBus\State\Service\StateActionBeforeService;
use Duyler\ActionBus\State\StateContext;

interface ActionBeforeStateHandlerInterface extends StateHandlerObservedInterface, StateHandlerInterface
{
    public function handle(StateActionBeforeService $stateService, StateContext $context): void;
}
