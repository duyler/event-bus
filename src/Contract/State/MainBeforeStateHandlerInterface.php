<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Contract\State;

use Duyler\ActionBus\State\Service\StateMainBeforeService;
use Duyler\ActionBus\State\StateContext;

interface MainBeforeStateHandlerInterface extends StateHandlerObservedInterface, StateHandlerInterface
{
    public function handle(StateMainBeforeService $stateService, StateContext $context): void;
}
