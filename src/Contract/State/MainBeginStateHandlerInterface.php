<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainBeginService;
use Duyler\EventBus\State\StateContext;

interface MainBeginStateHandlerInterface extends StateHandlerInterface
{
    public function handle(StateMainBeginService $stateService, StateContext $context): void;
}
