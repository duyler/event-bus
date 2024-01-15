<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainBeforeService;
use Duyler\EventBus\State\StateContext;

interface MainBeforeStateHandlerInterface extends StateHandlerObservedInterface, StateHandlerInterface
{
    public function handle(StateMainBeforeService $stateService, StateContext $context): void;
}
