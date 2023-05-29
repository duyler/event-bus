<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainFinalService;

interface StateMainFinalHandlerInterface
{
    public function handle(StateMainFinalService $stateService): void;
    public function prepare(): void;
}
