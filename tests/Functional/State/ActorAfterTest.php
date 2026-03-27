<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\Build\Context;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\ActorAfterStateHandlerInterface;
use Duyler\EventBus\State\Service\StateActorAfterService;
use Duyler\EventBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ActorAfterTest extends TestCase
{
    #[Test]
    public function after(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new ActorAfterStateHandler());
        $busBuilder->addStateContext(new Context(
            [ActorAfterStateHandler::class],
        ));
        $busBuilder->doActor(
            new Actor(
                id: 'Test',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build()->run();
        $this->assertTrue($bus->resultIsExists('Test'));
    }
}

class ActorAfterStateHandler implements ActorAfterStateHandlerInterface
{
    #[Override]
    public function handle(StateActorAfterService $stateService, StateContext $context): void
    {
        $stateService->getContainer();
        $stateService->getActor();
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [];
    }
}
