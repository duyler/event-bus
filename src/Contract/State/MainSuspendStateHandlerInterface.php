<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainSuspendService;
use Duyler\EventBus\State\StateContext;
use Duyler\EventBus\State\Suspend;

interface MainSuspendStateHandlerInterface extends StateHandlerInterface
{
    public function handle(StateMainSuspendService $stateService, StateContext $context): void;

    public function observed(Suspend $suspend, StateContext $context): bool;
}
