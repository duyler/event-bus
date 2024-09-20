<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Action\Context;
use Duyler\EventBus\Action\Exception\ActionHandlerMustBeCallableException;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use RuntimeException;
use stdClass;

class ActionHandlerTest extends TestCase
{
    #[Test]
    public function run_with_invalid_action_handler(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: 'string',
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();

        $this->expectException(ReflectionException::class);
        $bus->run();
    }

    #[Test]
    public function run_with_not_callable_action_handler(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: Handler::class,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();

        $this->expectException(ActionHandlerMustBeCallableException::class);
        $bus->run();
    }

    #[Test]
    public function run_with_callable_action_handler_with_context(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->addAction(
            new Action(
                id: 'TestDep',
                handler: fn(Context $context) => $context->definition(TestContract::class),
                contract: TestContract::class,
            ),
        );

        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: function (Context $context): stdClass {
                    $contract = $context->contract(TestContract::class);
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
                contract: stdClass::class,
            ),
        );

        $bus = $busBuilder->build();

        $bus->run();

        $this->assertEquals('Hello, Duyler!', $bus->getResult('Test')->data->helloDuyler);
    }

    #[Test]
    public function run_with_callable_action_handler_with_call_invalid_contract(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: function (Context $context): void {
                    $context->contract(TestContract::class);
                },
            ),
        );

        $bus = $busBuilder->build();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Addressing an invalid context from Test');

        $bus->run();
    }

    #[Test]
    public function run_with_callable_action_handler_with_invalid_dependency_type_hint(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: function (Context $context): void {
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
