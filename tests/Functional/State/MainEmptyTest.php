<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\Context;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainEmptyStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainEmptyService;
use Duyler\EventBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MainEmptyTest extends TestCase
{
    #[Test]
    public function end_with_result(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainEmptyStateHandler());
        $busBuilder->addStateContext(new Context(
            [MainEmptyStateHandler::class],
        ));
        $busBuilder->doAction(
            new Action(
                id: 'ActionFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build()->run();

        $this->assertTrue($bus->resultIsExists('ActionFromBuilder'));
    }
}

class MainEmptyStateHandler implements MainEmptyStateHandlerInterface
{
    #[Override]
    public function handle(StateMainEmptyService $stateService, StateContext $context): void
    {
        $stateService->resultIsExists('ActionFromBuilder');
        $stateService->getResult('ActionFromBuilder');
        'ActionFromBuilder' === $stateService->getFirstAction();
        'ActionFromBuilder' === $stateService->getLastAction();
    }
}
