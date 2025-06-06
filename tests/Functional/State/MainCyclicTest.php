<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\Build\Context;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainCyclicStateHandlerInterface;
use Duyler\EventBus\Dto\Event;
use Duyler\EventBus\Exception\CircularCallActionException;
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
        $busBuilder->doAction(
            new Action(
                id: 'TestAction',
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
        $busBuilder->addStateHandler(new MainCyclicStateHandlerWithTrigger());
        $busBuilder->addStateContext(new Context(
            [MainCyclicStateHandlerWithTrigger::class],
        ));
        $busBuilder->doAction(
            new Action(
                id: 'ActionFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $busBuilder->addEvent(new \Duyler\EventBus\Build\Event(id: 'EventFromHandler'));

        $bus = $busBuilder->build();
        $bus->run();
        $this->assertTrue($bus->resultIsExists('ActionFromBuilder'));
        $this->assertTrue($bus->resultIsExists('ActionFromHandler'));
    }

    #[Test]
    public function cyclic_with_lock_action(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainCyclicStateHandlerWithRepeatableEvent());
        $busBuilder->addStateContext(new Context(
            [MainCyclicStateHandlerWithRepeatableEvent::class],
        ));

        $busBuilder->addEvent(new \Duyler\EventBus\Build\Event(id: 'EventFromHandler'));

        $bus = $busBuilder->build();

        $this->expectException(CircularCallActionException::class);

        $bus->run();
        $this->assertTrue($bus->resultIsExists('ActionFromHandler'));
    }
}

class MainCyclicStateHandlerWithTrigger implements MainCyclicStateHandlerInterface
{
    #[Override]
    public function handle(StateMainCyclicService $stateService, StateContext $context): void
    {
        $stateService->addAction(
            new Action(
                id: 'ActionFromHandler',
                handler: function (): void {},
                listen: ['EventFromHandler'],
                externalAccess: true,
            ),
        );

        $stateService->dispatchEvent(
            new Event(
                id: 'EventFromHandler',
            ),
        );

        $stateService->inQueue('ActionFromBuilder');
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
        $stateService->addAction(
            new Action(
                id: 'ActionFromHandler',
                handler: function (): void {
                    Fiber::suspend();
                },
                listen: ['EventFromHandler'],
                externalAccess: true,
                repeatable: true,
                lock: true,
            ),
        );

        $stateService->dispatchEvent(
            new Event(
                id: 'EventFromHandler',
            ),
        );
    }
}
