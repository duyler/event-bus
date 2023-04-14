<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

interface StateHandlerInterface
{
    public function handle(StateStartService&StateBeforeService&StateAfterService&StateFinalService $stateService): void;
    public function observed(): array;
}
