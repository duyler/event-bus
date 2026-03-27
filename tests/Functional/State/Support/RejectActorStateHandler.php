<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State\Support;

use Duyler\EventBus\Contract\State\MainBeforeStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainBeforeService;
use Duyler\EventBus\State\StateContext;
use Override;

class RejectActorStateHandler implements MainBeforeStateHandlerInterface
{
    public function __construct(
        private readonly string $actorId,
    ) {}

    #[Override]
    public function handle(StateMainBeforeService $stateService, StateContext $context): void
    {
        $stateService->reject();
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [$this->actorId];
    }
}
