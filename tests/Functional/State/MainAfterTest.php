<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Context;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MainAfterTest extends TestCase
{
    #[Test]
    public function remove_action_from_state_handler(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainAfterStateHandler());
        $busBuilder->addStateContext(new Context(
            [MainAfterStateHandler::class]
        ));
        $busBuilder->doAction(
            new Action(
                id: 'ActionFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            )
        );

        $busBuilder->addAction(
            new Action(
                id: 'RemovedActionFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            )
        );

        $busBuilder->addAction(
            new Action(
                id: 'SubscribedActionFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            )
        );

        $busBuilder->addSubscription(
            new Subscription(
                subjectId: 'ActionFromBuilder',
                actionId: 'RemovedActionFromBuilder',
            )
        );

        $busBuilder->addSubscription(
            new Subscription(
                subjectId: 'RemovedActionFromBuilder',
                actionId: 'SubscribedActionFromBuilder',
            )
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('ActionFromBuilder'));
        $this->assertFalse($bus->resultIsExists('RemovedActionFromBuilder'));
        $this->assertFalse($bus->resultIsExists('SubscribedActionFromBuilder'));
    }
}

class MainAfterStateHandler implements MainAfterStateHandlerInterface
{
    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        if ($stateService->resultIsExists('ActionFromBuilder')) {
            $stateService->removeAction('RemovedActionFromBuilder');
        }
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return ['ActionFromBuilder'];
    }
}
