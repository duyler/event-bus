<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Contract\State;

use Duyler\ActionBus\State\Service\StateMainResumeService;
use Duyler\ActionBus\State\StateContext;

interface MainResumeStateHandlerInterface extends StateHandlerInterface
{
    public function handle(StateMainResumeService $stateService, StateContext $context): mixed;
}
