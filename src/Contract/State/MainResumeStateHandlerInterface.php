<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Contract\State;

use Duyler\ActionBus\State\Service\StateMainResumeService;
use Duyler\ActionBus\State\StateContext;
use Duyler\ActionBus\State\Suspend;

interface MainResumeStateHandlerInterface extends StateHandlerInterface
{
    public function handle(StateMainResumeService $stateService, StateContext $context): void;

    public function observed(Suspend $suspend, StateContext $context): bool;
}
