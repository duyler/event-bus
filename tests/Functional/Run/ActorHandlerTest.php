<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\DI\Exception\NotFoundException;
use Duyler\EventBus\Actor\Context\ActorContext;
use Duyler\EventBus\Actor\Exception\ActorHandlerMustBeCallableException;
use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class ActorHandlerTest extends TestCase
{
    #[Test]
    public function run_with_invalid_actor_handler(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->doActor(
            new Actor(
                id: 'Test',
                handler: 'string',
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();

        $this->expectException(NotFoundException::class);
        $bus->run();
    }

    #[Test]
    public function run_with_not_callable_actor_handler(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->doActor(
            new Actor(
                id: 'Test',
                handler: Handler::class,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();

        $this->expectException(ActorHandlerMustBeCallableException::class);
        $bus->run();
    }

    #[Test]
    public function run_with_callable_actor_handler_with_context(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->addActor(
            new Actor(
                id: 'TestDep',
                handler: fn(ActorContext $context) => $context->call(fn(TestContract $testContract) => $testContract),
                type: TestContract::class,
            ),
        );

        $busBuilder->doActor(
            new Actor(
                id: 'Test',
                handler: function (ActorContext $context): stdClass {
                    $contract = $context->argument();
                    $hello = $context->call(
                        fn(HandlerDependencyClass $dependencyClass) => $dependencyClass->get(),
                    );

                    $data = new stdClass();
                    $data->helloDuyler = $hello . ', ' . $contract->text . '!';
                    return $data;
                },
                required: [
                    'TestDep',
                ],
                argument: TestContract::class,
                type: stdClass::class,
                immutable: false,
            ),
        );

        $bus = $busBuilder->build();

        $bus->run();

        $this->assertEquals('Hello, Duyler!', $bus->getResult('Test')->data->helloDuyler);
    }

    #[Test]
    public function run_with_callable_actor_handler_with_call_invalid_contract(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->doActor(
            new Actor(
                id: 'Test',
                handler: function (ActorContext $context): void {
                    $context->argument();
                },
            ),
        );

        $bus = $busBuilder->build();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Argument not defined for actor Test');

        $bus->run();
    }

    #[Test]
    public function run_with_callable_actor_handler_with_invalid_dependency_type_hint(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->doActor(
            new Actor(
                id: 'Test',
                handler: function (ActorContext $context): void {
                    $context->call(
                        fn($dependencyClass) => $dependencyClass->get(),
                    );
                },
            ),
        );

        $bus = $busBuilder->build();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Type hint not set for dependencyClass');

        $bus->run();
    }
}

class Handler
{
    public function run(): void {}
}

class HandlerDependencyClass
{
    public function get(): string
    {
        return 'Hello';
    }
}

readonly class TestContract
{
    public function __construct(
        public string $text = 'Duyler',
    ) {}
}
