<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Actor\Context\ActorContext;
use Duyler\EventBus\Actor\Context\FactoryContext;
use Duyler\EventBus\Actor\Exception\InvalidArgumentFactoryException;
use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class ArgumentFactoryTest extends TestCase
{
    #[Test]
    public function run_actor_with_callback_factory(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder
            ->addActor(
                new Actor(
                    id: 'TestArgumentFactoryActor',
                    handler: fn() => new TestArgumentContract('Hello'),
                    type: TestArgumentContract::class,
                    externalAccess: true,
                ),
            )
            ->doActor(
                new Actor(
                    id: 'TestArgument',
                    handler: fn(ActorContext $context) => $context->argument(),
                    required: ['TestArgumentFactoryActor'],
                    argument: TestArgument::class,
                    argumentFactory: function (FactoryContext $context) {
                        $text = $context->call(
                            function (stdClass $text) {
                                $text->name = ' Duyler!';
                                return $text;
                            },
                        );
                        return new TestArgument($context->getTypeById('TestArgumentFactoryActor')->seyHello . $text->name);
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
    public function run_actor_with_callback_factory_with_invalid_contract(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder
            ->doActor(
                new Actor(
                    id: 'TestArgument',
                    handler: fn(ActorContext $context) => $context->argument(),
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
        $this->expectExceptionMessage('Type not defined with actor id ' . TestArgumentContract::class . ' for TestArgument factory');

        $builder->build()->run();
    }

    #[Test]
    public function run_actor_with_class_factory(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder
            ->addActor(
                new Actor(
                    id: 'TestArgumentFactoryActor',
                    handler: fn() => new TestArgumentContract('Hello'),
                    type: TestArgumentContract::class,
                    externalAccess: true,
                ),
            )
            ->doActor(
                new Actor(
                    id: 'TestArgument',
                    handler: fn(ActorContext $context) => $context->argument(),
                    required: ['TestArgumentFactoryActor'],
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
    public function run_actor_with_invalid_class_factory(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder
            ->addActor(
                new Actor(
                    id: 'TestArgumentFactoryActor',
                    handler: fn() => new TestArgumentContract('Hello'),
                    type: TestArgumentContract::class,
                    externalAccess: true,
                ),
            )
            ->doActor(
                new Actor(
                    id: 'TestArgument',
                    handler: fn(TestArgument $argument) => $argument,
                    required: ['TestArgumentFactoryActor'],
                    argument: TestArgument::class,
                    argumentFactory: stdClass::class,
                    type: TestArgument::class,
                    externalAccess: true,
                ),
            );

        $this->expectException(InvalidArgumentFactoryException::class);

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
        $contract = $context->getTypeById('TestArgumentFactoryActor');
        return new TestArgument($contract->seyHello . ' Duyler! With class factory');
    }
}
