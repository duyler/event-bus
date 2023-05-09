<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateStartService;
use Duyler\EventBus\State\StateHandlerInterface;

interface StateStartHandlerInterface extends StateHandlerInterface
{
    public function handle(StateStartService $stateService): void;
}
