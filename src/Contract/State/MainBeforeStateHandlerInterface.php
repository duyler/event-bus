<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainBeforeService;

interface MainBeforeStateHandlerInterface extends
    StateHandlerObservedInterface,
    StateHandlerInterface
{
    public function handle(StateMainBeforeService $stateService): void;
}
