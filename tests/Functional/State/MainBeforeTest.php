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
        $busBuilder->addStateHandler(new MainBeforeStateHandler());
        $busBuilder->addStateContext(new Context(
            [MainBeforeStateHandler::class]
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
}

class MainBeforeStateHandler implements MainBeforeStateHandlerInterface
{
    #[Override]
    public function handle(StateMainBeforeService $stateService, StateContext $context): void
    {
        $stateService->substituteHandler(
            actionId:'ActionFromBuilder',
            handlerSubstitution: NewHandler::class
        );
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return ['ActionFromBuilder'];
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
