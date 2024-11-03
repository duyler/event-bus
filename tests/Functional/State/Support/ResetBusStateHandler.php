<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State\Support;

use Duyler\EventBus\Contract\State\MainCyclicStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainCyclicService;
use Duyler\EventBus\State\StateContext;
use Override;

class ResetBusStateHandler implements MainCyclicStateHandlerInterface
{
    #[Override]
    public function handle(StateMainCyclicService $stateService, StateContext $context): void
    {
        $stateService->reset();
    }
}
