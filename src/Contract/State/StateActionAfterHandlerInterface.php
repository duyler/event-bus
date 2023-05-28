<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateActionAfterService;
use Duyler\EventBus\State\StateHandlerInterface;
use Duyler\EventBus\State\StateHandlerObservedInterface;

interface StateActionAfterHandlerInterface extends StateHandlerInterface, StateHandlerObservedInterface
{
    public function handle(StateActionAfterService $stateService): void;
}
