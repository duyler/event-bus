<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Actor\Context\ActorContext;
use Duyler\EventBus\Actor\Context\CustomContextInterface;
use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class CustomContextTest extends TestCase
{
    #[Test]
    public function run_with_callable_actor_handler_with_custom_context(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->addActor(
            new Actor(
                id: 'TestDep',
                handler: function (ActorContext $context): void {},
            ),
        );

        $busBuilder->doActor(
            new Actor(
                id: 'Test',
                handler: function (CustomContext $context): stdClass {
                    $hello = $context->getHello();

                    $data = new stdClass();
                    $data->helloDuyler = $hello . ', ' . 'Duyler!';
                    return $data;
                },
                required: [
                    'TestDep',
                ],
                context: CustomContext::class,
                type: stdClass::class,
                immutable: false,
            ),
        );

        $bus = $busBuilder->build();

        $bus->run();

        $this->assertEquals('Hello, Duyler!', $bus->getResult('Test')->data->helloDuyler);
    }

    #[Test]
    public function run_with_callable_actor_handler_with_exception(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->addActor(
            new Actor(
                id: 'TestDep',
                handler: function (ActorContext $context): void {},
            ),
        );

        $busBuilder->doActor(
            new Actor(
                id: 'Test',
                handler: function (CustomContext $context): stdClass {
                    $hello = $context->getHello();

                    $data = new stdClass();
                    $data->helloDuyler = $hello . ', ' . 'Duyler!';
                    return $data;
                },
                required: [
                    'TestDep',
                ],
                context: InvalidCustomContext::class,
                type: stdClass::class,
                immutable: false,
            ),
        );

        $bus = $busBuilder->build();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom context class must implement ' . CustomContextInterface::class);

        $bus->run();
    }
}

class CustomContext implements CustomContextInterface
{
    public function __construct(
        private readonly ActorContext $actorContext,
    ) {}

    public function getHello(): string
    {
        return 'Hello';
    }
}

class InvalidCustomContext
{
    public function __construct(
        private readonly ActorContext $actorContext,
    ) {}
}
