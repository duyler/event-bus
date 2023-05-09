<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\State\Service\StateAfterService;
use Duyler\EventBus\State\Service\StateBeforeService;
use Duyler\EventBus\State\Service\StateFinalService;
use Duyler\EventBus\State\Service\StateStartService;
use Duyler\EventBus\State\Service\StateSuspendService;

interface StateHandlerInterface
{
    public function handle(StateStartService&StateBeforeService&StateSuspendService&StateAfterService&StateFinalService $stateService): void;
    public function observed(): array;
}
