<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainBeforeStateHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\ActionHandlerSubstitution;
use Duyler\EventBus\Dto\ActionResultSubstitution;
use Duyler\EventBus\Dto\Context;
use Duyler\EventBus\State\Service\StateMainBeforeService;
use Duyler\EventBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MainBeforeTest extends TestCase
{
    #[Test]
    public function run_with_substitute_handler_from_state_handler(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainBeforeStateHandlerWithSubstituteActionHandler());
        $busBuilder->addStateContext(new Context(
            [MainBeforeStateHandlerWithSubstituteActionHandler::class]
        ));
        $busBuilder->doAction(
            new Action(
                id: 'ActionFromBuilder_1',
                handler: fn(): ResultInterface => new class () implements ResultInterface {},
                contract: ResultInterface::class,
                externalAccess: true,
            )
        );

        $busBuilder->doAction(
            new Action(
                id: 'ActionFromBuilder_2',
                handler: fn(): ResultInterface => new class () implements ResultInterface {},
                contract: ResultInterface::class,
                externalAccess: true,
            )
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('ActionFromBuilder_1'));
        $this->assertTrue($bus->resultIsExists('ActionFromBuilder_2'));
        $this->assertEquals('Value from new result 1', $bus->getResult('ActionFromBuilder_1')->data->value);
        $this->assertEquals('Value from new result 2', $bus->getResult('ActionFromBuilder_2')->data->value);
    }

    #[Test]
    public function run_with_substitute_result_from_state_handler(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainBeforeStateHandlerWithSubstituteActionRequiredResult());
        $busBuilder->addStateContext(new Context(
            [MainBeforeStateHandlerWithSubstituteActionRequiredResult::class]
        ));
        $busBuilder->doAction(
            new Action(
                id: 'RequiredAction',
                handler: fn(): ResultInterface => new class () implements ResultInterface {},
                contract: ResultInterface::class,
                externalAccess: true,
            )
        );
        $busBuilder->doAction(
            new Action(
                id: 'ActionWithRequired',
                handler: fn(ResultInterface $result): ResultInterface => $result,
                required: [
                    'RequiredAction',
                ],
                argument: ResultInterface::class,
                contract: ResultInterface::class,
                externalAccess: true,
            )
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
        $stateService->substituteHandler(
            new ActionHandlerSubstitution(
                actionId: 'ActionFromBuilder_1',
                handler: NewHandler::class,
            )
        );

        $stateService->substituteHandler(
            new ActionHandlerSubstitution(
                actionId: 'ActionFromBuilder_2',
                handler: fn() => new NewResult('Value from new result 2'),
            )
        );
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return ['ActionFromBuilder_1', 'ActionFromBuilder_2'];
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
                requiredContract: ResultInterface::class,
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
