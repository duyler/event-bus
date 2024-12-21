<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State\Support;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Contract\State\MainUnresolvedStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainUnresolvedService;
use Duyler\EventBus\State\StateContext;
use Override;

class HandleUnresolvedTaskStateHandler implements MainUnresolvedStateHandlerInterface
{
    #[Override]
    public function handle(StateMainUnresolvedService $stateService, StateContext $context): void
    {
        $stateService->doAction(
            new Action(
                id: 'ActionFromStateHandler',
                handler: function () {},
            ),
        );

        $stateService->getActionId();
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [];
    }
}
