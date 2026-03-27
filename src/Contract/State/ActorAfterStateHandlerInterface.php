<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateActorAfterService;
use Duyler\EventBus\State\StateContext;

interface ActorAfterStateHandlerInterface extends StateHandlerObservedInterface, StateHandlerInterface
{
    public function handle(StateActorAfterService $stateService, StateContext $context): void;
}
