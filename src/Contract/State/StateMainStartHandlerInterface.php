<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainStartService;
use Duyler\EventBus\State\StateHandlerPreparedInterface;

interface StateMainStartHandlerInterface extends StateHandlerPreparedInterface
{
    public function handle(StateMainStartService $stateService): void;
}
