<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Contract\State;

use Duyler\ActionBus\State\Service\StateMainCyclicService;
use Duyler\ActionBus\State\StateContext;

interface MainCyclicStateHandlerInterface extends StateHandlerInterface
{
    public function handle(StateMainCyclicService $stateService, StateContext $context): void;
}
