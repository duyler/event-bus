<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\Build\ActorHandlerSubstitution;
use Duyler\EventBus\Build\ActorResultSubstitution;
use Duyler\EventBus\Build\Context;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainBeforeStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainBeforeService;
use Duyler\EventBus\State\StateContext;
use Duyler\EventBus\Test\Functional\State\Support\FlushSuccessLogStateHandler;
use Duyler\EventBus\Test\Functional\State\Support\RejectActorStateHandler;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MainBeforeTest extends TestCase
{
    #[Test]
    public function flushSuccessLog_from_state_handler(): void
    {
        $flushSuccessLogStateHandler = new FlushSuccessLogStateHandler('TestActor');
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler($flushSuccessLogStateHandler);
        $busBuilder->doActor(
            new Actor(
                id: 'TestActor',
                handler: function (): void {},
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('TestActor'));
    }

    #[Test]
    public function reject_actor()
    {
        $rejectStateHandler = new RejectActorStateHandler('RejectActor');
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler($rejectStateHandler);
        $busBuilder->doActor(
            new Actor(
                id: 'RejectActor',
                handler: function (): void {},
            ),
        );

        $busBuilder->doActor(
            new Actor(
                id: 'NotRejectActor',
                handler: function (): void {},
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('NotRejectActor'));
        $this->assertFalse($bus->resultIsExists('RejectActor'));
    }

    #[Test]
    public function run_with_substitute_handler_from_state_handler(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainBeforeStateHandlerWithSubstituteActorHandler());
        $busBuilder->addStateContext(new Context(
            [MainBeforeStateHandlerWithSubstituteActorHandler::class],
        ));
        $busBuilder->doActor(
            new Actor(
                id: TestActorEnum::ActorFromBuilder_1,
                handler: fn(): ResultInterface => new class implements ResultInterface {},
                type: ResultInterface::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $busBuilder->doActor(
            new Actor(
                id: TestActorEnum::ActorFromBuilder_2,
                handler: fn(): ResultInterface => new class implements ResultInterface {},
                type: ResultInterface::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists(TestActorEnum::ActorFromBuilder_1));
        $this->assertEquals(
            'Value from new result 1',
            $bus->getResult(TestActorEnum::ActorFromBuilder_1)->data->value,
        );
        $this->assertEquals(
            'Value from new result 2',
            $bus->getResult(TestActorEnum::ActorFromBuilder_2)->data->value,
        );
    }

    #[Test]
    public function run_with_substitute_result_from_state_handler(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainBeforeStateHandlerWithSubstituteActorRequiredResult());
        $busBuilder->addStateContext(new Context(
            [MainBeforeStateHandlerWithSubstituteActorRequiredResult::class],
        ));
        $busBuilder->doActor(
            new Actor(
                id: 'RequiredActor',
                handler: fn(): ResultInterface => new class implements ResultInterface {},
                type: ResultInterface::class,
                immutable: false,
                externalAccess: true,
            ),
        );
        $busBuilder->doActor(
            new Actor(
                id: 'ActorWithRequired',
                handler: fn(\Duyler\EventBus\Actor\Context\ActorContext $context): ResultInterface => $context->argument(),
                required: [
                    'RequiredActor',
                ],
                argument: ResultInterface::class,
                type: ResultInterface::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('ActorWithRequired'));
        $this->assertEquals('Value from substitute result', $bus->getResult('ActorWithRequired')->data->value);
    }
}

class MainBeforeStateHandlerWithSubstituteActorHandler implements MainBeforeStateHandlerInterface
{
    #[Override]
    public function handle(StateMainBeforeService $stateService, StateContext $context): void
    {
        if ($stateService->getActorId() === TestActorEnum::ActorFromBuilder_1) {
            $stateService->substituteHandler(
                new ActorHandlerSubstitution(
                    actorId: TestActorEnum::ActorFromBuilder_1,
                    handler: NewHandler::class,
                ),
            );
        }

        if ($stateService->getActorId() === TestActorEnum::ActorFromBuilder_2) {
            $stateService->substituteHandler(
                new ActorHandlerSubstitution(
                    actorId: TestActorEnum::ActorFromBuilder_2,
                    handler: fn() => new NewResult('Value from new result 2'),
                ),
            );
        }
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [TestActorEnum::ActorFromBuilder_1, TestActorEnum::ActorFromBuilder_2];
    }
}

class MainBeforeStateHandlerWithSubstituteActorRequiredResult implements MainBeforeStateHandlerInterface
{
    #[Override]
    public function handle(StateMainBeforeService $stateService, StateContext $context): void
    {
        $stateService->substituteResult(
            new ActorResultSubstitution(
                actorId: $stateService->getActorId(),
                requiredActorId: 'RequiredActor',
                substitution: new NewResult('Value from substitute result'),
            ),
        );
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return ['ActorWithRequired'];
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

enum TestActorEnum
{
    case ActorFromBuilder_1;
    case ActorFromBuilder_2;
}
