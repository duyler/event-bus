<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainEndStateHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\State\Service\StateMainEndService;
use Duyler\EventBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MailEndTest extends TestCase
{
    #[Test]
    public function end_with_result(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainEndStateHandler());
        $busBuilder->doAction(
            new Action(
                id: 'ActionFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            )
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
        $stateService->getFirstAction() === 'ActionFromBuilder';
        $stateService->getLastAction() === 'ActionFromBuilder';
    }
}
