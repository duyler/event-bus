<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainCyclicService;
use Duyler\EventBus\State\StateContext;

interface MainCyclicStateHandlerInterface extends StateHandlerInterface
{
    public function handle(StateMainCyclicService $stateService, StateContext $context): void;
}
