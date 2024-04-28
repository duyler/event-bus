<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Functional\State;

use Duyler\ActionBus\BusBuilder;
use Duyler\ActionBus\BusConfig;
use Duyler\ActionBus\Contract\State\ActionAfterStateHandlerInterface;
use Duyler\ActionBus\Dto\Action;
use Duyler\ActionBus\Dto\Context;
use Duyler\ActionBus\State\Service\StateActionAfterService;
use Duyler\ActionBus\State\StateContext;
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
