<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\ActionAfterStateHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Context;
use Duyler\EventBus\State\Service\StateActionAfterService;
use Duyler\EventBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ActionAfterTest extends TestCase
{
    #[Test]
    public function after(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new ActionAfterStateHandler());
        $busBuilder->addStateContext(new Context(
            [ActionAfterStateHandler::class],
        ));
        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: function () {},
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build()->run();
        $this->assertTrue($bus->resultIsExists('Test'));
    }
}

class ActionAfterStateHandler implements ActionAfterStateHandlerInterface
{
    #[Override]
    public function handle(StateActionAfterService $stateService, StateContext $context): void
    {
        $stateService->getContainer();
        $stateService->getAction();
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [];
    }
}
