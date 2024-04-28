<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Contract\State;

use Duyler\ActionBus\State\Service\StateMainBeginService;
use Duyler\ActionBus\State\StateContext;

interface MainBeginStateHandlerInterface extends StateHandlerInterface
{
    public function handle(StateMainBeginService $stateService, StateContext $context): void;
}
