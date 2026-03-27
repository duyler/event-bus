<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\Build\Context;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainBeginStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainBeginService;
use Duyler\EventBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MainBeginTest extends TestCase
{
    #[Test]
    public function run_with_add_actor_from_state_handler(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainBeginStateHandler());
        $busBuilder->addStateContext(new Context(
            [MainBeginStateHandler::class],
        ));
        $busBuilder->doActor(
            new Actor(
                id: 'ActorFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('ActorFromBuilder'));
    }

    #[Test]
    public function run_with_get_actor_from_state_handler(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainBeginStateHandlerWithGetAndDoActor());
        $busBuilder->addStateContext(new Context(
            [MainBeginStateHandlerWithGetAndDoActor::class],
        ));
        $busBuilder->addActor(
            new Actor(
                id: 'ActorFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('ActorFromBuilder'));
        $this->assertTrue($bus->resultIsExists('ActorFromStateMainBegin'));
    }
}

class MainBeginStateHandler implements MainBeginStateHandlerInterface
{
    #[Override]
    public function handle(StateMainBeginService $stateService, StateContext $context): void
    {
        $stateService->addActor(
            new Actor(
                id: 'ActorFromStateMainBegin',
                handler: function (): void {},
                externalAccess: true,
            ),
        );
    }
}

class MainBeginStateHandlerWithGetAndDoActor implements MainBeginStateHandlerInterface
{
    #[Override]
    public function handle(StateMainBeginService $stateService, StateContext $context): void
    {
        if ($stateService->actorIsExists('ActorFromBuilder')) {
            $actor = $stateService->getById('ActorFromBuilder');
            $stateService->doExistsActor($actor->id);
        }

        $stateService->doActor(
            new Actor(
                id: 'ActorFromStateMainBegin',
                handler: function (): void {},
            ),
        );
    }
}
