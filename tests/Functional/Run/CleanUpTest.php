<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\Build\Event;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\Contract\State\MainBeginStateHandlerInterface;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\Service\StateMainBeginService;
use Duyler\EventBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CleanUpTest extends TestCase
{
    #[Test]
    public function clean_up_max_count_events_and_actors(): void
    {
        $busBuilder = new BusBuilder(
            new BusConfig(
                maxCountCompleteActors: 1,
                maxCountEvents: 1,
            ),
        );

        $busBuilder->addStateHandler(new AddDynamicEventsAndActorsStateHandler());
        $busBuilder->addStateHandler(new DispatchEventsStateHandler());

        $busBuilder->doActor(
            new Actor(
                id: 'ActorFromBuilder',
                handler: function (): void {},
            ),
        );

        $bus = $busBuilder->build();

        $bus->run();

        $this->assertTrue($bus->resultIsExists('ActorFromBuilder'));
        //$this->assertFalse($bus->resultIsExists('RemovableActorOne'));
        $this->assertFalse($bus->resultIsExists('RemovableActorTwo'));
    }
}

class AddDynamicEventsAndActorsStateHandler implements MainBeginStateHandlerInterface
{
    #[Override]
    public function handle(StateMainBeginService $stateService, StateContext $context): void
    {
        $stateService->registerEvent(
            new Event(
                id: 'RemovableEventOne',
            ),
        );

        $stateService->registerEvent(
            new Event(
                id: 'RemovableEventTwo',
            ),
        );

        $stateService->addActor(
            new Actor(
                id: 'RemovableActorOne',
                handler: function (): void {},
                onOne: 'RemovableEventOne' . IdFormatter::DELIMITER . 'Success',
            ),
        );

        $stateService->addActor(
            new Actor(
                id: 'RemovableActorTwo',
                handler: function (): void {},
                required: [
                    'RemovableActorOne',
                ],
                onOne: 'RemovableEventTwo' . IdFormatter::DELIMITER . 'Success',
            ),
        );
    }
}

class DispatchEventsStateHandler implements MainAfterStateHandlerInterface
{
    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        $stateService->dispatchEvent(new \Duyler\EventBus\Dto\Event(
            'RemovableEventOne',
        ));

        $stateService->dispatchEvent(new \Duyler\EventBus\Dto\Event(
            'RemovableEventTwo',
        ));
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return ['ActorFromBuilder'];
    }
}
