<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\ActionHandlerSubstitution;
use Duyler\EventBus\Build\ActionResultSubstitution;
use Duyler\EventBus\Build\Context;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainBeforeStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainBeforeService;
use Duyler\EventBus\State\StateContext;
use Duyler\EventBus\Test\Functional\State\Support\FlushSuccessLogStateHandler;
use Duyler\EventBus\Test\Functional\State\Support\RejectActionStateHandler;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MainBeforeTest extends TestCase
{
    #[Test]
    public function flushSuccessLog_from_state_handler(): void
    {
        $flushSuccessLogStateHandler = new FlushSuccessLogStateHandler('TestAction');
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler($flushSuccessLogStateHandler);
        $busBuilder->doAction(
            new Action(
                id: 'TestAction',
                handler: function () {},
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('TestAction'));
    }

    #[Test]
    public function reject_action()
    {
        $rejectStateHandler = new RejectActionStateHandler('RejectAction');
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler($rejectStateHandler);
        $busBuilder->doAction(
            new Action(
                id: 'RejectAction',
                handler: function () {},
            ),
        );

        $busBuilder->doAction(
            new Action(
                id: 'NotRejectAction',
                handler: function () {},
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('NotRejectAction'));
        $this->assertFalse($bus->resultIsExists('RejectAction'));
    }

    #[Test]
    public function run_with_substitute_handler_from_state_handler(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainBeforeStateHandlerWithSubstituteActionHandler());
        $busBuilder->addStateContext(new Context(
            [MainBeforeStateHandlerWithSubstituteActionHandler::class],
        ));
        $busBuilder->doAction(
            new Action(
                id: TestActionEnum::ActionFromBuilder_1,
                handler: fn(): ResultInterface => new class implements ResultInterface {},
                type: ResultInterface::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $busBuilder->doAction(
            new Action(
                id: TestActionEnum::ActionFromBuilder_2,
                handler: fn(): ResultInterface => new class implements ResultInterface {},
                type: ResultInterface::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists(TestActionEnum::ActionFromBuilder_1));
        $this->assertTrue($bus->resultIsExists(TestActionEnum::ActionFromBuilder_2));
        $this->assertEquals(
            'Value from new result 1',
            $bus->getResult(TestActionEnum::ActionFromBuilder_1)->data->value,
        );
        $this->assertEquals(
            'Value from new result 2',
            $bus->getResult(TestActionEnum::ActionFromBuilder_2)->data->value,
        );
    }

    #[Test]
    public function run_with_substitute_result_from_state_handler(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainBeforeStateHandlerWithSubstituteActionRequiredResult());
        $busBuilder->addStateContext(new Context(
            [MainBeforeStateHandlerWithSubstituteActionRequiredResult::class],
        ));
        $busBuilder->doAction(
            new Action(
                id: 'RequiredAction',
                handler: fn(): ResultInterface => new class implements ResultInterface {},
                type: ResultInterface::class,
                immutable: false,
                externalAccess: true,
            ),
        );
        $busBuilder->doAction(
            new Action(
                id: 'ActionWithRequired',
                handler: fn(\Duyler\EventBus\Action\Context\ActionContext $context): ResultInterface => $context->argument(),
                required: [
                    'RequiredAction',
                ],
                argument: ResultInterface::class,
                type: ResultInterface::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('ActionWithRequired'));
        $this->assertEquals('Value from substitute result', $bus->getResult('ActionWithRequired')->data->value);
    }
}

class MainBeforeStateHandlerWithSubstituteActionHandler implements MainBeforeStateHandlerInterface
{
    #[Override]
    public function handle(StateMainBeforeService $stateService, StateContext $context): void
    {
        if ($stateService->getActionId() === TestActionEnum::ActionFromBuilder_1) {
            $stateService->substituteHandler(
                new ActionHandlerSubstitution(
                    actionId: TestActionEnum::ActionFromBuilder_1,
                    handler: NewHandler::class,
                ),
            );
        }

        if ($stateService->getActionId() === TestActionEnum::ActionFromBuilder_2) {
            $stateService->substituteHandler(
                new ActionHandlerSubstitution(
                    actionId: TestActionEnum::ActionFromBuilder_2,
                    handler: fn() => new NewResult('Value from new result 2'),
                ),
            );
        }
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [TestActionEnum::ActionFromBuilder_1, TestActionEnum::ActionFromBuilder_2];
    }
}

class MainBeforeStateHandlerWithSubstituteActionRequiredResult implements MainBeforeStateHandlerInterface
{
    #[Override]
    public function handle(StateMainBeforeService $stateService, StateContext $context): void
    {
        $stateService->substituteResult(
            new ActionResultSubstitution(
                actionId: $stateService->getActionId(),
                requiredActionId: 'RequiredAction',
                substitution: new NewResult('Value from substitute result'),
            ),
        );
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return ['ActionWithRequired'];
    }
}

class NewHandler
{
    public function __invoke()
    {
        return new NewResult('Value from new result 1');
    }
}

class NewResult implements ResultInterface
{
    public function __construct(public string $value) {}
}

interface ResultInterface {}

enum TestActionEnum
{
    case ActionFromBuilder_1;
    case ActionFromBuilder_2;
}
