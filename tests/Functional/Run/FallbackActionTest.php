<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Exception\UnableToContinueWithFailActionException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class FallbackActionTest extends TestCase
{
    #[Test]
    public function run_with_fallback_action()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: function (): void {},
                required: ['RequiredAction'],
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'RequiredAction',
                handler: fn() => Result::fail(),
                required: [],
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
                fallbacks: [
                    'FallbackRequiredAction',
                ],
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'FallbackRequiredAction',
                handler: fn() => new stdClass(),
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build()->run();

        $this->assertTrue($bus->resultIsExists('Test'));
        $this->assertTrue($bus->resultIsExists('RequiredAction'));
        $this->assertTrue($bus->resultIsExists('FallbackRequiredAction'));
    }

    #[Test]
    public function run_with_fallback_action_with_not_allowed_skip()
    {
        $busBuilder = new BusBuilder(new BusConfig(allowSkipUnresolvedActions: false));
        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: function (): void {},
                required: ['RequiredAction'],
                externalAccess: true,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'RequiredAction',
                handler: fn() => Result::fail(),
                required: [],
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
                fallbacks: [
                    'FallbackRequiredAction',
                ],
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'FallbackRequiredAction',
                handler: fn() => Result::fail(),
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $this->expectException(UnableToContinueWithFailActionException::class);

        $bus = $busBuilder->build()->run();

        $this->assertFalse($bus->resultIsExists('Test'));
        $this->assertTrue($bus->resultIsExists('RequiredAction'));
        $this->assertTrue($bus->resultIsExists('FallbackRequiredAction'));
    }

    #[Test]
    public function run_with_fallback_action_with_allowed_skip()
    {
        $busBuilder = new BusBuilder(new BusConfig(allowSkipUnresolvedActions: true));
        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: function (): void {},
                required: ['RequiredAction'],
                externalAccess: true,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'RequiredAction',
                handler: fn() => Result::fail(),
                required: [],
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
                fallbacks: [
                    'FallbackRequiredAction',
                ],
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'FallbackRequiredAction',
                handler: fn() => Result::fail(),
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
                retries: 3,
            ),
        );

        $bus = $busBuilder->build()->run();

        $this->assertFalse($bus->resultIsExists('Test'));
        $this->assertTrue($bus->resultIsExists('RequiredAction'));
        $this->assertTrue($bus->resultIsExists('FallbackRequiredAction'));
    }

    #[Test]
    public function run_with_retrying_action()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: function (): void {},
                required: ['RequiredAction'],
                externalAccess: true,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'RequiredAction',
                handler: fn() => Result::fail(),
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
                fallbacks: [
                    'FallbackRequiredAction',
                ],
                retries: 2,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'FallbackRequiredAction',
                handler: fn() => new stdClass(),
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build()->run();

        $this->assertTrue($bus->resultIsExists('Test'));
        $this->assertTrue($bus->resultIsExists('RequiredAction'));
        $this->assertTrue($bus->resultIsExists('FallbackRequiredAction'));
    }
}
