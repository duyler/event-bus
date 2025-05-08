<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Action\Context\ActionContext;
use Duyler\EventBus\Action\Context\FactoryContext;
use Duyler\EventBus\Action\Exception\InvalidArgumentFactoryException;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class ArgumentFactoryTest extends TestCase
{
    #[Test]
    public function run_action_with_callback_factory(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder
            ->addAction(
                new Action(
                    id: 'TestArgumentFactoryAction',
                    handler: fn() => new TestArgumentContract('Hello'),
                    type: TestArgumentContract::class,
                    externalAccess: true,
                ),
            )
            ->doAction(
                new Action(
                    id: 'TestArgument',
                    handler: fn(ActionContext $context) => $context->argument(),
                    required: ['TestArgumentFactoryAction'],
                    argument: TestArgument::class,
                    argumentFactory: function (FactoryContext $context) {
                        $text = $context->call(
                            function (stdClass $text) {
                                $text->name = ' Duyler!';
                                return $text;
                            },
                        );
                        return new TestArgument($context->getType('TestArgumentFactoryAction')->seyHello . $text->name);
                    },
                    type: TestArgument::class,
                    externalAccess: true,
                ),
            );

        $bus = $builder->build()->run();

        $this->assertInstanceOf(TestArgument::class, $bus->getResult('TestArgument')->data);
        $this->assertEquals('Hello Duyler!', $bus->getResult('TestArgument')->data->seyHelloWithName);
    }

    #[Test]
    public function run_action_with_callback_factory_with_invalid_contract(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder
            ->doAction(
                new Action(
                    id: 'TestArgument',
                    handler: fn(ActionContext $context) => $context->argument(),
                    argument: TestArgument::class,
                    argumentFactory: function (FactoryContext $context) {
                        $contract = $context->getType(TestArgumentContract::class);
                        $text = $context->call(
                            function (stdClass $text) {
                                $text->name = ' Duyler!';
                                return $text;
                            },
                        );
                        return new TestArgument('Hello, ' . $text->name);
                    },
                    type: TestArgument::class,
                    externalAccess: true,
                ),
            );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Addressing an invalid context from TestArgument');

        $builder->build()->run();
    }

    #[Test]
    public function run_action_with_class_factory(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder
            ->addAction(
                new Action(
                    id: 'TestArgumentFactoryAction',
                    handler: fn() => new TestArgumentContract('Hello'),
                    type: TestArgumentContract::class,
                    externalAccess: true,
                ),
            )
            ->doAction(
                new Action(
                    id: 'TestArgument',
                    handler: fn(ActionContext $context) => $context->argument(),
                    required: ['TestArgumentFactoryAction'],
                    argument: TestArgument::class,
                    argumentFactory: ArgumentFactory::class,
                    type: TestArgument::class,
                    externalAccess: true,
                ),
            );

        $bus = $builder->build()->run();

        $this->assertInstanceOf(TestArgument::class, $bus->getResult('TestArgument')->data);
        $this->assertEquals('Hello Duyler! With class factory', $bus->getResult('TestArgument')->data->seyHelloWithName);
    }

    #[Test]
    public function run_action_with_invalid_class_factory(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder
            ->addAction(
                new Action(
                    id: 'TestArgumentFactoryAction',
                    handler: fn() => new TestArgumentContract('Hello'),
                    type: TestArgumentContract::class,
                    externalAccess: true,
                ),
            )
            ->doAction(
                new Action(
                    id: 'TestArgument',
                    handler: fn(TestArgument $argument) => $argument,
                    required: ['TestArgumentFactoryAction'],
                    argument: TestArgument::class,
                    argumentFactory: stdClass::class,
                    type: TestArgument::class,
                    externalAccess: true,
                ),
            );

        $this->expectException(InvalidArgumentFactoryException::class);

        $bus = $builder->build()->run();
    }
}

readonly class TestArgument
{
    public function __construct(public string $seyHelloWithName) {}
}

readonly class TestArgumentContract
{
    public function __construct(public string $seyHello) {}
}

class ArgumentFactory
{
    public function __invoke(FactoryContext $context): TestArgument
    {
        $contract = $context->getType('TestArgumentFactoryAction');
        return new TestArgument($contract->seyHello . ' Duyler! With class factory');
    }
}
