<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainBeforeService;
use Duyler\EventBus\State\StateHandlerInterface;

interface StateMainBeforeHandlerInterface extends StateHandlerInterface
{
    public function handle(StateMainBeforeService $stateService): void;
}
