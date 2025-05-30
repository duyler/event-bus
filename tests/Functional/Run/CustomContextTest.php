<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Action\Context\ActionContext;
use Duyler\EventBus\Action\Context\CustomContextInterface;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class CustomContextTest extends TestCase
{
    #[Test]
    public function run_with_callable_action_handler_with_custom_context(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->addAction(
            new Action(
                id: 'TestDep',
                handler: function (ActionContext $context): void {},
            ),
        );

        $busBuilder->doAction(
            new Action(
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
    public function run_with_callable_action_handler_with_exception(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->addAction(
            new Action(
                id: 'TestDep',
                handler: function (ActionContext $context): void {},
            ),
        );

        $busBuilder->doAction(
            new Action(
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
        private readonly ActionContext $actionContext,
    ) {}

    public function getHello(): string
    {
        return 'Hello';
    }
}

class InvalidCustomContext
{
    public function __construct(
        private readonly ActionContext $actionContext,
    ) {}
}
