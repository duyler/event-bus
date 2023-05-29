<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\StateHandlerInterface;
use Duyler\EventBus\State\StateHandlerObservedInterface;

interface StateMainAfterHandlerInterface extends StateHandlerInterface, StateHandlerObservedInterface
{
    public function handle(StateMainAfterService $stateService): void;
}
