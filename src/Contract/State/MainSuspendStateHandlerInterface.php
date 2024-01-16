<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\Service\StateMainSuspendService;
use Duyler\EventBus\State\StateContext;

interface MainSuspendStateHandlerInterface extends StateHandlerInterface
{
    public function handle(StateMainSuspendService $stateService, StateContext $context): mixed;

    public function isResumable(mixed $value): bool;
}
