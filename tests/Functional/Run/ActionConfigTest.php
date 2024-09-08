<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\SharedService;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\DependencyInjection\Provider\AbstractProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ActionConfigTest extends TestCase
{
    #[Test]
    public function run_action_with_container_binding_config()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'TestAction',
                handler: TestHandler::class,
                config: [
                    TestInterface::class => NeedleClass::class,
                ],
                contract: TestInterface::class,
            ),
        );

        $bus = $busBuilder->build();

        $result = $bus->run()->getResult('TestAction');

        $this->assertInstanceOf(TestInterface::class, $result->data);
    }

    #[Test]
    public function run_action_with_container_provider_config()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'TestAction',
                handler: TestHandler::class,
                config: [
                    TestInterface::class => TestProvider::class,
                ],
                contract: TestInterface::class,
            ),
        );

        $bus = $busBuilder->build();

        $result = $bus->run()->getResult('TestAction');

        $this->assertInstanceOf(TestInterface::class, $result->data);
    }

    #[Test]
    public function run_action_with_container_definition_config()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'TestAction',
                handler: TestHandler::class,
                config: [
                    TestInterface::class => TestProvider::class,
                    NeedleClass::class => [
                        'key' => 'DefKey',
                        'value' => 'DefValue',
                    ],
                ],
                contract: TestInterface::class,
            ),
        );

        $bus = $busBuilder->build();

        $result = $bus->run()->getResult('TestAction');

        $this->assertInstanceOf(TestInterface::class, $result->data);
        $this->assertEquals('DefKey', $result->data->key);
        $this->assertEquals('DefValue', $result->data->value);
    }

    #[Test]
    public function run_action_with_container_config_and_shared_service()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'TestAction',
                handler: TestHandler::class,
                config: [
                    TestInterface::class => TestProvider::class,
                    NeedleClass::class => [
                        'key' => 'DefKey',
                        'value' => 'DefValue',
                    ],
                ],
                contract: TestInterface::class,
            ),
        );

        $busBuilder->addSharedService(
            new SharedService(
                NeedleClass::class,
                new NeedleClass(
                    key: 'SharedKey',
                    value: 'SharedValue',
                ),
                bind: [
                    TestInterface::class => NeedleClass::class,
                ],
            ),
        );

        $bus = $busBuilder->build();

        $result = $bus->run()->getResult('TestAction');

        $this->assertInstanceOf(TestInterface::class, $result->data);
        $this->assertEquals('DefKey', $result->data->key);
        $this->assertEquals('DefValue', $result->data->value);
    }
}

class NeedleClass implements TestInterface
{
    public function __construct(
        public string $key = 'TestKey',
        public string $value = 'TestValue',
    ) {}
}

interface TestInterface {}

class TestProvider extends AbstractProvider
{
    public function bind(): array
    {
        return [TestInterface::class => NeedleClass::class];
    }
}

class TestHandler
{
    public function __construct(public TestInterface $needle) {}

    public function __invoke(): TestInterface
    {
        return $this->needle;
    }
}
