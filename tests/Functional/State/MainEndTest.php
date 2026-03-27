<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\Build\Context;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainEndStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainEndService;
use Duyler\EventBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MainEndTest extends TestCase
{
    #[Test]
    public function end_with_result(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainEndStateHandler());
        $busBuilder->addStateContext(new Context(
            [MainEndStateHandler::class],
        ));
        $busBuilder->doActor(
            new Actor(
                id: 'ActorFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build()->run();

        $this->assertTrue($bus->resultIsExists('ActorFromBuilder'));
    }
}

class MainEndStateHandler implements MainEndStateHandlerInterface
{
    #[Override]
    public function handle(StateMainEndService $stateService, StateContext $context): void
    {
        $stateService->resultIsExists('ActorFromBuilder');
        $stateService->getResult('ActorFromBuilder');
        'ActorFromBuilder' === $stateService->getFirstActor();
        'ActorFromBuilder' === $stateService->getLastActor();
    }
}
