<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Action;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

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
                    contract: TestArgumentContract::class,
                    externalAccess: true,
                )
            )
            ->doAction(
                new Action(
                    id: 'TestArgument',
                    handler: fn(TestArgument $argument) => $argument,
                    required: ['TestArgumentFactoryAction'],
                    argument: TestArgument::class,
                    argumentFactory: fn(TestArgumentContract $contract)
                        => new TestArgument($contract->seyHello . ' Duyler! With callback factory'),
                    contract: TestArgument::class,
                    externalAccess: true,
                )
            );

        $bus = $builder->build()->run();

        $this->assertInstanceOf(TestArgument::class, $bus->getResult('TestArgument')->data);
        $this->assertEquals('Hello Duyler! With callback factory', $bus->getResult('TestArgument')->data->seyHelloWithName);
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
                    contract: TestArgumentContract::class,
                    externalAccess: true,
                )
            )
            ->doAction(
                new Action(
                    id: 'TestArgument',
                    handler: fn(TestArgument $argument) => $argument,
                    required: ['TestArgumentFactoryAction'],
                    argument: TestArgument::class,
                    argumentFactory: ArgumentFactory::class,
                    contract: TestArgument::class,
                    externalAccess: true,
                )
            );

        $bus = $builder->build()->run();

        $this->assertInstanceOf(TestArgument::class, $bus->getResult('TestArgument')->data);
        $this->assertEquals('Hello Duyler! With class factory', $bus->getResult('TestArgument')->data->seyHelloWithName);
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
    public function __invoke(TestArgumentContract $contract)
    {
        return new TestArgument($contract->seyHello . ' Duyler! With class factory');
    }
}
