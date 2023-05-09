<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateAfterService;
use Duyler\EventBus\State\StateHandlerInterface;

interface StateAfterHandlerInterface extends StateHandlerInterface
{
    public function handle(StateAfterService $stateService): void;
}
