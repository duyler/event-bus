<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\Build\Context;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\ActorThrowingStateHandlerInterface;
use Duyler\EventBus\State\Service\StateActorThrowingService;
use Duyler\EventBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ActorThrowingTest extends TestCase
{
    #[Test]
    public function throw_without_rollback(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new ActorThrowingStateHandler());
        $busBuilder->addStateContext(new Context(
            [ActorThrowingStateHandler::class],
        ));
        $busBuilder->doActor(
            new Actor(
                id: 'Test',
                handler: function (): void {
                    throw new RuntimeException('Test exception message');
                },
                externalAccess: true,
            ),
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Test exception message');
        $busBuilder->build()->run();
    }
}

class ActorThrowingStateHandler implements ActorThrowingStateHandlerInterface
{
    #[Override]
    public function handle(StateActorThrowingService $stateService, StateContext $context): void
    {
        $stateService->getException();
        $stateService->getActor();
        $stateService->getContainer();
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [];
    }
}
