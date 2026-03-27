<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\Build\Context;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainCyclicStateHandlerInterface;
use Duyler\EventBus\Dto\Event;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\State\Service\StateMainCyclicService;
use Duyler\EventBus\State\StateContext;
use Duyler\EventBus\Test\Functional\State\Support\ResetBusStateHandler;
use Fiber;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MainCyclicTest extends TestCase
{
    #[Test]
    public function reset_from_state_handler(): void
    {
        $resetBusStateHandler = new ResetBusStateHandler();
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler($resetBusStateHandler);
        $busBuilder->doActor(
            new Actor(
                id: 'TestActor',
                handler: function (): void {},
            ),
        );

        $bus = $busBuilder->build();

        $this->expectExceptionMessage('TaskQueue is empty');

        $bus->run();
    }

    #[Test]
    public function cyclic_with_event(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainCyclicStateHandlerWithEvent());
        $busBuilder->addStateContext(new Context(
            [MainCyclicStateHandlerWithEvent::class],
        ));
        $busBuilder->doActor(
            new Actor(
                id: 'ActorFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $busBuilder->addEvent(new \Duyler\EventBus\Build\Event(id: 'EventFromHandler'));

        $bus = $busBuilder->build();
        $bus->run();
        $this->assertTrue($bus->resultIsExists('ActorFromBuilder'));
        $this->assertTrue($bus->resultIsExists('ActorFromHandler'));
    }

    #[Test]
    public function cyclic_with_lock_actor(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainCyclicStateHandlerWithRepeatableEvent());
        $busBuilder->addStateContext(new Context(
            [MainCyclicStateHandlerWithRepeatableEvent::class],
        ));

        $busBuilder->doActor(
            new Actor(
                id: 'ActorFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $busBuilder->addEvent(new \Duyler\EventBus\Build\Event(id: 'EventFromHandler'));

        $bus = $busBuilder->build();

        $bus->dispatchEvent(new Event(
            id: 'EventFromHandler',
        ));

        $bus->run();
        $this->assertTrue($bus->resultIsExists('ActorFromHandler'));
    }
}

class MainCyclicStateHandlerWithEvent implements MainCyclicStateHandlerInterface
{
    #[Override]
    public function handle(StateMainCyclicService $stateService, StateContext $context): void
    {
        if (false === $stateService->actorIsExists('ActorFromHandler')) {
            $stateService->addActor(
                new Actor(
                    id: 'ActorFromHandler',
                    handler: function (): void {},
                    onOne: 'EventFromHandler' . IdFormatter::DELIMITER . 'Success',
                    externalAccess: true,
                ),
            );
        }

        if (false === $stateService->resultIsExists('ActorFromHandler')) {
            $stateService->dispatchEvent(
                new Event(
                    id: 'EventFromHandler',
                ),
            );
        }

        $stateService->inQueue('ActorFromBuilder');
        $stateService->queueIsEmpty();
        $stateService->queueIsNotEmpty();
        $stateService->queueCount();
    }
}

class MainCyclicStateHandlerWithRepeatableEvent implements MainCyclicStateHandlerInterface
{
    #[Override]
    public function handle(StateMainCyclicService $stateService, StateContext $context): void
    {
        if (false === $stateService->actorIsExists('ActorFromHandler')) {
            $stateService->addActor(
                new Actor(
                    id: 'ActorFromHandler',
                    handler: function (): void {
                        Fiber::suspend();
                    },
                    onOne: 'EventFromHandler' . IdFormatter::DELIMITER . 'Success',
                    externalAccess: true,
                    repeatable: true,
                    lock: true,
                ),
            );
        }

        if (false === $stateService->resultIsExists('ActorFromHandler')) {
            $stateService->dispatchEvent(
                new Event(
                    id: 'EventFromHandler',
                ),
            );
        }
    }
}
