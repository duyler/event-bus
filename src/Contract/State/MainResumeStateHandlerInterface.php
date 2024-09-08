<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainResumeService;
use Duyler\EventBus\State\StateContext;
use Duyler\EventBus\State\Suspend;

interface MainResumeStateHandlerInterface extends StateHandlerInterface
{
    public function handle(StateMainResumeService $stateService, StateContext $context): void;

    public function observed(Suspend $suspend, StateContext $context): bool;
}
