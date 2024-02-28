<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainBeginStateHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Context;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\State\Service\StateMainBeginService;
use Duyler\EventBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MainBeginTest extends TestCase
{
    #[Test]
    public function run_with_add_action_from_state_handler(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainBeginStateHandler());
        $busBuilder->addStateContext(new Context(
            [MainBeginStateHandler::class]
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
        $this->assertTrue($bus->resultIsExists('ActionFromStateMainBegin'));
    }
}

class MainBeginStateHandler implements MainBeginStateHandlerInterface
{
    #[Override]
    public function handle(StateMainBeginService $stateService, StateContext $context): void
    {
        $stateService->addAction(
            new Action(
                id: 'ActionFromStateMainBegin',
                handler: function (): void {},
                externalAccess: true,
            )
        );

        $stateService->addSubscription(
            new Subscription(
                subjectId: 'ActionFromBuilder',
                actionId: 'ActionFromStateMainBegin',
            )
        );
    }
}
