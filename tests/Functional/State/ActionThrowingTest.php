<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Functional\State;

use Duyler\ActionBus\BusBuilder;
use Duyler\ActionBus\BusConfig;
use Duyler\ActionBus\Contract\State\ActionThrowingStateHandlerInterface;
use Duyler\ActionBus\Dto\Action;
use Duyler\ActionBus\Dto\Context;
use Duyler\ActionBus\State\Service\StateActionThrowingService;
use Duyler\ActionBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ActionThrowingTest extends TestCase
{
    #[Test]
    public function throw_without_rollback(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new ActionThrowingStateHandler());
        $busBuilder->addStateContext(new Context(
            [ActionThrowingStateHandler::class],
        ));
        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: function () {throw new RuntimeException('Test exception message'); },
                externalAccess: true,
            ),
        );

        $busBuilder->build();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Test exception message');
        $busBuilder->build()->run();
    }
}

class ActionThrowingStateHandler implements ActionThrowingStateHandlerInterface
{
    #[Override]
    public function handle(StateActionThrowingService $stateService, StateContext $context): void
    {
        $stateService->getException();
        $stateService->getAction();
        $stateService->getContainer();
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [];
    }
}
