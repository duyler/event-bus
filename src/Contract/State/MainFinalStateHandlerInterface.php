<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainFinalService;
use Duyler\EventBus\State\StateHandlerInterface;

interface MainFinalStateHandlerInterface extends
    StateHandlerInterface
{
    public function handle(StateMainFinalService $stateService): void;
}
