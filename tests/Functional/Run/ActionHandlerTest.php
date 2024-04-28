<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Functional\Run;

use Duyler\ActionBus\Action\Exception\ActionHandlerMustBeCallableException;
use Duyler\ActionBus\BusBuilder;
use Duyler\ActionBus\BusConfig;
use Duyler\ActionBus\Dto\Action;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;

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
}

class Handler
{
    public function run(): void {}
}
