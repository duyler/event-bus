<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateActionBeforeService;
use Duyler\EventBus\State\StateHandlerInterface;

interface StateActionBeforeHandlerInterface extends StateHandlerInterface
{
    public function handle(StateActionBeforeService $stateService): void;
}
