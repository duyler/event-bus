<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainEmptyService;
use Duyler\EventBus\State\StateContext;

interface MainEmptyStateHandlerInterface extends StateHandlerInterface
{
    public function handle(StateMainEmptyService $stateService, StateContext $context): void;
}
