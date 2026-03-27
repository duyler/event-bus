<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State\Support;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\Contract\State\MainUnresolvedStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainUnresolvedService;
use Duyler\EventBus\State\StateContext;
use Override;

class HandleUnresolvedTaskStateHandler implements MainUnresolvedStateHandlerInterface
{
    #[Override]
    public function handle(StateMainUnresolvedService $stateService, StateContext $context): void
    {
        $stateService->doActor(
            new Actor(
                id: 'ActorFromStateHandler',
                handler: function (): void {},
            ),
        );

        $stateService->getActorId();
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [];
    }
}
