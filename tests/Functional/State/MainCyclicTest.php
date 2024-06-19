<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Functional\State;

use Duyler\ActionBus\Build\Action;
use Duyler\ActionBus\Build\Context;
use Duyler\ActionBus\BusBuilder;
use Duyler\ActionBus\BusConfig;
use Duyler\ActionBus\Contract\State\MainCyclicStateHandlerInterface;
use Duyler\ActionBus\Dto\Event;
use Duyler\ActionBus\Exception\CircularCallActionException;
use Duyler\ActionBus\State\Service\StateMainCyclicService;
use Duyler\ActionBus\State\StateContext;
use Fiber;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MainCyclicTest extends TestCase
{
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

        $bus = $busBuilder->build();
        $bus->run();
        $this->assertTrue($bus->resultIsExists('ActionFromBuilder'));
        $this->assertTrue($bus->resultIsExists('ActionFromHandler'));
    }

    #[Test]
    public function cyclic_with_lock_action(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainCyclicStateHandlerWithRepeatableTrigger());
        $busBuilder->addStateContext(new Context(
            [MainCyclicStateHandlerWithRepeatableTrigger::class],
        ));

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
                listen: 'TriggerFromHandler',
                externalAccess: true,
            ),
        );

        $stateService->dispatchEvent(
            new Event(
                id: 'TriggerFromHandler',
            ),
        );

        $stateService->inQueue('ActionFromBuilder');
        $stateService->queueIsEmpty();
        $stateService->queueIsNotEmpty();
        $stateService->queueCount();
    }
}

class MainCyclicStateHandlerWithRepeatableTrigger implements MainCyclicStateHandlerInterface
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
                listen: 'TriggerFromHandler',
                externalAccess: true,
                repeatable: true,
                lock: true,
            ),
        );

        $stateService->dispatchEvent(
            new Event(
                id: 'TriggerFromHandler',
            ),
        );
    }
}
