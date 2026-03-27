<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateActorThrowingService;
use Duyler\EventBus\State\StateContext;

interface ActorThrowingStateHandlerInterface extends StateHandlerObservedInterface, StateHandlerInterface
{
    public function handle(StateActorThrowingService $stateService, StateContext $context): void;
}
