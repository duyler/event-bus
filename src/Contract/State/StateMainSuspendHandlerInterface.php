<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainSuspendService;
use Duyler\EventBus\State\StateHandlerPreparedInterface;

interface StateMainSuspendHandlerInterface extends StateHandlerPreparedInterface
{
    public function getResume(StateMainSuspendService $stateService): mixed;
}
