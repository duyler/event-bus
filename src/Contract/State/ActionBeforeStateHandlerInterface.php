<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateActionBeforeService;
use Duyler\EventBus\State\StateContext;

interface ActionBeforeStateHandlerInterface extends StateHandlerObservedInterface, StateHandlerInterface
{
    public function handle(StateActionBeforeService $stateService, StateContext $context): void;
}
