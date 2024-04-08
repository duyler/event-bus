<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainCyclicStateHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Context;
use Duyler\EventBus\Dto\Trigger;
use Duyler\EventBus\Exception\CircularCallActionException;
use Duyler\EventBus\State\Service\StateMainCyclicService;
use Duyler\EventBus\State\StateContext;
use Fiber;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MainCyclicTest extends TestCase
{
    #[Test]
    public function cyclic_with_trigger(): void
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
                triggeredOn: 'TriggerFromHandler',
                externalAccess: true,
            ),
        );

        $stateService->doTrigger(
            new Trigger(
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
                triggeredOn: 'TriggerFromHandler',
                externalAccess: true,
                repeatable: true,
                lock: true,
            ),
        );

        $stateService->doTrigger(
            new Trigger(
                id: 'TriggerFromHandler',
            ),
        );
    }
}
