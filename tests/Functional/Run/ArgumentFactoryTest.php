<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use ArrayIterator;
use Duyler\EventBus\Action\Context\ActionContext;
use Duyler\EventBus\Action\Context\FactoryContext;
use Duyler\EventBus\Action\Exception\InvalidArgumentFactoryException;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\Type;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
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
                        return new TestArgument($context->getTypeById('TestArgumentFactoryAction')->seyHello . $text->name);
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
                        $contract = $context->getTypeById(TestArgumentContract::class);
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
        $this->expectExceptionMessage('Type not defined with action id ' . TestArgumentContract::class . ' for TestArgument factory');

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

        $builder->build()->run();
    }

    #[Test]
    public function run_action_with_callback_factory_with_depends_on_type(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder
            ->doAction(
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
                    dependsOn: [Type::of(TestArgumentContract::class)],
                    argument: TestArgument::class,
                    argumentFactory: function (FactoryContext $context) {
                        $text = $context->call(
                            function (stdClass $text) {
                                $text->name = ' Duyler!';
                                return $text;
                            },
                        );
                        return new TestArgument($context->getType(TestArgumentContract::class)->seyHello . $text->name);
                    },
                    type: TestArgument::class,
                    externalAccess: true,
                ),
            )->doAction(
                new Action(
                    id: 'TestReturnDepend',
                    handler: fn(ActionContext $context) => new ArrayIterator([$context->argument()]),
                    dependsOn: [Type::collectionOf(stdClass::class)],
                    argument: ArrayIterator::class,
                    type: stdClass::class,
                    typeCollection: ArrayIterator::class,
                    immutable: false,
                ),
            )->doAction(
                new Action(
                    id: 'TestReturn',
                    handler: fn() => new ArrayIterator([new stdClass()]),
                    type: stdClass::class,
                    typeCollection: ArrayIterator::class,
                    immutable: false,
                ),
            )->doAction(
                new Action(
                    id: 'TestReturnEqual',
                    handler: fn() => new ArrayIterator([new stdClass()]),
                    type: stdClass::class,
                    typeCollection: ArrayIterator::class,
                    immutable: false,
                ),
            )->doAction(
                new Action(
                    id: 'TestDepend',
                    handler: function (ActionContext $context) {},
                    dependsOn: [Type::collectionOf(stdClass::class)],
                    argument: ArrayIterator::class,
                    argumentFactory: function (FactoryContext $context) {
                        return $context->getTypeCollection(stdClass::class);
                    },
                ),
            );

        $bus = $builder->build()->run();

        $this->assertInstanceOf(TestArgument::class, $bus->getResult('TestArgument')->data);
        $this->assertEquals('Hello Duyler!', $bus->getResult('TestArgument')->data->seyHelloWithName);
        $this->assertTrue($bus->resultIsExists('TestDepend'));
        $this->assertInstanceOf(ArrayIterator::class, $bus->getResult('TestReturnDepend')->data);
    }

    #[Test]
    public function run_action_with_factory_exception_type(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder
            ->doAction(
                new Action(
                    id: 'TestArgument',
                    handler: fn(ActionContext $context) => $context->argument(),
                    argument: TestArgument::class,
                    argumentFactory: function (FactoryContext $context) {
                        $context->getType(stdClass::class);
                    },
                    type: TestArgument::class,
                    externalAccess: true,
                ),
            );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Type ' . stdClass::class . ' not defined for TestArgument factory');

        $builder->build()->run();
    }

    #[Test]
    public function run_action_with_factory_exception_type_collection(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder
            ->doAction(
                new Action(
                    id: 'TestArgument',
                    handler: fn(ActionContext $context) => $context->argument(),
                    argument: TestArgument::class,
                    argumentFactory: function (FactoryContext $context) {
                        $context->getTypeCollection(stdClass::class);
                    },
                    type: TestArgument::class,
                    externalAccess: true,
                ),
            );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Type collection not defined with type: ' . stdClass::class . ' for TestArgument factory');

        $builder->build()->run();
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
        $contract = $context->getTypeById('TestArgumentFactoryAction');
        return new TestArgument($contract->seyHello . ' Duyler! With class factory');
    }
}
