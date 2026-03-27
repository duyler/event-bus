<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\Build\Context;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\ActorBeforeStateHandlerInterface;
use Duyler\EventBus\State\Service\StateActorBeforeService;
use Duyler\EventBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class ActorBeforeTest extends TestCase
{
    #[Test]
    public function before(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new ActorBeforeStateHandler());
        $busBuilder->addStateContext(new Context(
            [ActorBeforeStateHandler::class],
        ));
        $busBuilder->doActor(
            new Actor(
                id: 'Test',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $busBuilder->doActor(
            new Actor(
                id: TestActor::TestArgumentReturn,
                handler: fn(): stdClass => new stdClass(),
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $busBuilder->doActor(
            new Actor(
                id: TestActor::TestArgument,
                handler: function (\Duyler\EventBus\Actor\Context\ActorContext $argument): void {},
                required: [TestActor::TestArgumentReturn],
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build()->run();
        $this->assertTrue($bus->resultIsExists('Test'));
    }
}

class ActorBeforeStateHandler implements ActorBeforeStateHandlerInterface
{
    #[Override]
    public function handle(StateActorBeforeService $stateService, StateContext $context): void
    {
        $stateService->getContainer();
        $stateService->getActor();
        $stateService->getArgument();
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return ['Test', TestActor::TestArgumentReturn];
    }
}

enum TestActor
{
    case TestArgumentReturn;
    case TestArgument;
}
