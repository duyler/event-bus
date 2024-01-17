<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainEndService;
use Duyler\EventBus\State\StateContext;

interface MainEndStateHandlerInterface extends StateHandlerInterface
{
    public function handle(StateMainEndService $stateService, StateContext $context): void;
}
