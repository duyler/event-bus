<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Contract\State;

use Duyler\ActionBus\State\Service\StateMainSuspendService;
use Duyler\ActionBus\State\StateContext;
use Duyler\ActionBus\State\Suspend;

interface MainSuspendStateHandlerInterface extends StateHandlerInterface
{
    public function handle(StateMainSuspendService $stateService, StateContext $context): void;

    public function observed(Suspend $suspend, StateContext $context): bool;
}
