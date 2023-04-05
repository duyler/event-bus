<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

use Duyler\EventBus\StateService;

interface StateHandlerInterface
{
    public function handle(StateService $stateService): void;
}
