<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateActorBeforeService;
use Duyler\EventBus\State\StateContext;

interface ActorBeforeStateHandlerInterface extends StateHandlerObservedInterface, StateHandlerInterface
{
    public function handle(StateActorBeforeService $stateService, StateContext $context): void;
}
