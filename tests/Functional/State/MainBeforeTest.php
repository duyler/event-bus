<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainBeforeStateHandlerInterface;
use Duyler\EventBus\Dto\Action;
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
                id: 'ActionFromBuilder',
                handler: fn(): ResultInterface => new class () implements ResultInterface {},
                contract: ResultInterface::class,
                externalAccess: true,
            )
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('ActionFromBuilder'));
        $this->assertEquals('Value from new result', $bus->getResult('ActionFromBuilder')->data->value);
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
            actionId: 'ActionFromBuilder',
            handlerSubstitution: NewHandler::class
        );
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return ['ActionFromBuilder'];
    }
}

class MainBeforeStateHandlerWithSubstituteActionRequiredResult implements MainBeforeStateHandlerInterface
{
    #[Override]
    public function handle(StateMainBeforeService $stateService, StateContext $context): void
    {
        $stateService->substituteResult(
            actionId: $stateService->getActionId(),
            substitutions: [
                ResultInterface::class => new NewResult('Value from substitute result'),
            ]
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
        return new NewResult('Value from new result');
    }
}

class NewResult implements ResultInterface
{
    public function __construct(public string $value) {}
}

interface ResultInterface {}
