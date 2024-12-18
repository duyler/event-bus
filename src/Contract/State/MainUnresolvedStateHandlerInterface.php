<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainUnresolvedService;
use Duyler\EventBus\State\StateContext;

interface MainUnresolvedStateHandlerInterface extends StateHandlerObservedInterface, StateHandlerInterface
{
    public function handle(StateMainUnresolvedService $stateService, StateContext $context): void;
}
