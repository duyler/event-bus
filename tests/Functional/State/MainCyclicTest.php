<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainCyclicStateHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Context;
use Duyler\EventBus\Dto\Trigger;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\State\Service\StateMainCyclicService;
use Duyler\EventBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MainCyclicTest extends TestCase
{
    #[Test]
    public function cyclic(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainCyclicStateHandler());
        $busBuilder->addStateContext(new Context(
            [MainCyclicStateHandler::class]
        ));
        $busBuilder->doAction(
            new Action(
                id: 'ActionFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            )
        );

        $bus = $busBuilder->build();
        $bus->run();
        $this->assertTrue($bus->resultIsExists('ActionFromBuilder'));
        $this->assertTrue($bus->resultIsExists('ActionFromHandler'));
    }
}

class MainCyclicStateHandler implements MainCyclicStateHandlerInterface
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
            )
        );

        $stateService->doTrigger(
            new Trigger(
                id: 'TriggerFromHandler',
                status: ResultStatus::Success,
            )
        );

        $stateService->inQueue('ActionFromBuilder');
        $stateService->queueIsEmpty();
        $stateService->queueIsNotEmpty();
    }
}
