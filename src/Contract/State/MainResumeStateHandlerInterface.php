<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainResumeService;

interface MainResumeStateHandlerInterface extends StateHandlerInterface
{
    public function handle(StateMainResumeService $stateService): mixed;
}
