<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\Build\Context;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainEmptyStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainEmptyService;
use Duyler\EventBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MainEmptyTest extends TestCase
{
    #[Test]
    public function end_with_result(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainEmptyStateHandler());
        $busBuilder->addStateContext(new Context(
            [MainEmptyStateHandler::class],
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

class MainEmptyStateHandler implements MainEmptyStateHandlerInterface
{
    #[Override]
    public function handle(StateMainEmptyService $stateService, StateContext $context): void
    {
        $stateService->resultIsExists('ActorFromBuilder');
        $stateService->getResult('ActorFromBuilder');
        'ActorFromBuilder' === $stateService->getFirstActor();
        'ActorFromBuilder' === $stateService->getLastActor();
    }
}
