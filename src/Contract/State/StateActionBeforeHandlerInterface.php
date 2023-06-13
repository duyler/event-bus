<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateActionBeforeService;
use Duyler\EventBus\State\StateHandlerInterface;
use Duyler\EventBus\State\StateHandlerPreparedInterface;
use Duyler\EventBus\State\StateHandlerObservedInterface;

interface StateActionBeforeHandlerInterface extends
    StateHandlerPreparedInterface,
    StateHandlerObservedInterface,
    StateHandlerInterface
{
    public function handle(StateActionBeforeService $stateService): void;
}
