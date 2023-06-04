<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainFinalService;
use Duyler\EventBus\State\StateHandlerPreparedInterface;

interface StateMainFinalHandlerInterface extends StateHandlerPreparedInterface
{
    public function handle(StateMainFinalService $stateService): void;
}
