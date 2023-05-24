<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateActionAfterService;
use Duyler\EventBus\State\StateHandlerInterface;

interface StateActionAfterHandlerInterface extends StateHandlerInterface
{
    public function handle(StateActionAfterService $stateService): void;
}
