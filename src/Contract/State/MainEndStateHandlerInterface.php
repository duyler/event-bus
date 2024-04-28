<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Contract\State;

use Duyler\ActionBus\State\Service\StateMainEndService;
use Duyler\ActionBus\State\StateContext;

interface MainEndStateHandlerInterface extends StateHandlerInterface
{
    public function handle(StateMainEndService $stateService, StateContext $context): void;
}
