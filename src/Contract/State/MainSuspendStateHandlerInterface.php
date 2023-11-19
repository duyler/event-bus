<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainSuspendService;

interface MainSuspendStateHandlerInterface extends
    StateHandlerInterface
{
    public function getResume(StateMainSuspendService $stateService): mixed;
}
