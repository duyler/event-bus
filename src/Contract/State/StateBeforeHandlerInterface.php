<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateBeforeService;
use Duyler\EventBus\State\StateHandlerInterface;

interface StateBeforeHandlerInterface extends StateHandlerInterface
{
    public function handle(StateBeforeService $stateService): void;
}
