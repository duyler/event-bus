<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Action;
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
    public function clean_up_max_count_events_and_actions(): void
    {
        $busBuilder = new BusBuilder(
            new BusConfig(
                maxCountCompleteActions: 1,
                maxCountEvents: 1,
            ),
        );

        $busBuilder->addStateHandler(new AddDynamicEventsAndActionsStateHandler());
        $busBuilder->addStateHandler(new DispatchEventsStateHandler());

        $busBuilder->doAction(
            new Action(
                id: 'ActionFromBuilder',
                handler: function (): void {},
            ),
        );

        $bus = $busBuilder->build();

        $bus->run();

        $this->assertTrue($bus->resultIsExists('ActionFromBuilder'));
        $this->assertFalse($bus->resultIsExists('RemovableActionOne'));
        $this->assertFalse($bus->resultIsExists('RemovableActionTwo'));
    }
}

class AddDynamicEventsAndActionsStateHandler implements MainBeginStateHandlerInterface
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

        $stateService->addAction(
            new Action(
                id: 'RemovableActionOne',
                handler: function (): void {},
                onOne: 'RemovableEventOne' . IdFormatter::DELIMITER . 'Success',
            ),
        );

        $stateService->addAction(
            new Action(
                id: 'RemovableActionTwo',
                handler: function (): void {},
                required: [
                    'RemovableActionOne',
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
            'RemovableEventOne' . IdFormatter::DELIMITER . 'Success',
        ));

        $stateService->dispatchEvent(new \Duyler\EventBus\Dto\Event(
            'RemovableEventTwo' . IdFormatter::DELIMITER . 'Success',
        ));
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return ['ActionFromBuilder'];
    }
}
