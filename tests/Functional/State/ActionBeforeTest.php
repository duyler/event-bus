<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\ActionBeforeStateHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Context;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\State\Service\StateActionBeforeService;
use Duyler\EventBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class ActionBeforeTest extends TestCase
{
    #[Test]
    public function before(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new ActionBeforeStateHandler());
        $busBuilder->addStateContext(new Context(
            [ActionBeforeStateHandler::class],
        ));
        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: function () {},
                externalAccess: true,
            ),
        );

        $busBuilder->doAction(
            new Action(
                id: TestAction::TestArgumentReturn,
                handler: fn(): stdClass => new stdClass(),
                contract: stdClass::class,
                externalAccess: true,
            ),
        );

        $busBuilder->doAction(
            new Action(
                id: TestAction::TestArgument,
                handler: function (stdClass $argument) {},
                required: [TestAction::TestArgumentReturn],
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build()->run();
        $this->assertTrue($bus->resultIsExists('Test'));
    }
}

class ActionBeforeStateHandler implements ActionBeforeStateHandlerInterface
{
    #[Override]
    public function handle(StateActionBeforeService $stateService, StateContext $context): void
    {
        $stateService->getContainer();
        $stateService->getAction();
        $stateService->getArgument();
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return ['Test', TestAction::TestArgumentReturn];
    }
}

class ActionBeforeThrowsStateHandler implements ActionBeforeStateHandlerInterface
{
    #[Override]
    public function handle(StateActionBeforeService $stateService, StateContext $context): void
    {
        $stateService->removeSubscription(new Subscription(
            subjectId: 'TestNotExists',
            actionId: 'Test',
        ));
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [];
    }
}

enum TestAction
{
    case TestArgumentReturn;
    case TestArgument;
}
