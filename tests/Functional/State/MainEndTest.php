<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Functional\State;

use Duyler\ActionBus\Build\Action;
use Duyler\ActionBus\Build\Context;
use Duyler\ActionBus\BusBuilder;
use Duyler\ActionBus\BusConfig;
use Duyler\ActionBus\Contract\State\MainEndStateHandlerInterface;
use Duyler\ActionBus\State\Service\StateMainEndService;
use Duyler\ActionBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MainEndTest extends TestCase
{
    #[Test]
    public function end_with_result(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainEndStateHandler());
        $busBuilder->addStateContext(new Context(
            [MainEndStateHandler::class],
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

class MainEndStateHandler implements MainEndStateHandlerInterface
{
    #[Override]
    public function handle(StateMainEndService $stateService, StateContext $context): void
    {
        $stateService->resultIsExists('ActionFromBuilder');
        $stateService->getResult('ActionFromBuilder');
        'ActionFromBuilder' === $stateService->getFirstAction();
        'ActionFromBuilder' === $stateService->getLastAction();
    }
}
