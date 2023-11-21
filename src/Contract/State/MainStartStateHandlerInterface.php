<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainStartService;

interface MainStartStateHandlerInterface extends StateHandlerInterface
{
    public function handle(StateMainStartService $stateService): void;
}
