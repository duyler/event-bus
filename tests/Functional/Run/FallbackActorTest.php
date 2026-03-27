<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Exception\UnableToContinueWithFailActorException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class FallbackActorTest extends TestCase
{
    #[Test]
    public function run_with_fallback_actor()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doActor(
            new Actor(
                id: 'Test',
                handler: function (): void {},
                required: ['RequiredActor'],
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $busBuilder->addActor(
            new Actor(
                id: 'RequiredActor',
                handler: fn() => Result::fail(),
                required: [],
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
                fallbacks: [
                    'FallbackRequiredActor',
                ],
            ),
        );

        $busBuilder->addActor(
            new Actor(
                id: 'FallbackRequiredActor',
                handler: fn() => new stdClass(),
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build()->run();

        $this->assertTrue($bus->resultIsExists('Test'));
        $this->assertTrue($bus->resultIsExists('RequiredActor'));
        $this->assertTrue($bus->resultIsExists('FallbackRequiredActor'));
    }

    #[Test]
    public function run_with_fallback_actor_with_not_allowed_skip()
    {
        $busBuilder = new BusBuilder(new BusConfig(allowSkipUnresolvedActors: false));
        $busBuilder->doActor(
            new Actor(
                id: 'Test',
                handler: function (): void {},
                required: ['RequiredActor'],
                externalAccess: true,
            ),
        );

        $busBuilder->addActor(
            new Actor(
                id: 'RequiredActor',
                handler: fn() => Result::fail(),
                required: [],
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
                fallbacks: [
                    'FallbackRequiredActor',
                ],
            ),
        );

        $busBuilder->addActor(
            new Actor(
                id: 'FallbackRequiredActor',
                handler: fn() => Result::fail(),
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $this->expectException(UnableToContinueWithFailActorException::class);

        $bus = $busBuilder->build()->run();

        $this->assertFalse($bus->resultIsExists('Test'));
        $this->assertTrue($bus->resultIsExists('RequiredActor'));
        $this->assertTrue($bus->resultIsExists('FallbackRequiredActor'));
    }

    #[Test]
    public function run_with_fallback_actor_with_allowed_skip()
    {
        $busBuilder = new BusBuilder(new BusConfig(allowSkipUnresolvedActors: true));
        $busBuilder->doActor(
            new Actor(
                id: 'Test',
                handler: function (): void {},
                required: ['RequiredActor'],
                externalAccess: true,
            ),
        );

        $busBuilder->addActor(
            new Actor(
                id: 'RequiredActor',
                handler: fn() => Result::fail(),
                required: [],
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
                fallbacks: [
                    'FallbackRequiredActor',
                ],
            ),
        );

        $busBuilder->addActor(
            new Actor(
                id: 'FallbackRequiredActor',
                handler: fn() => Result::fail(),
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
                retries: 3,
            ),
        );

        $bus = $busBuilder->build()->run();

        $this->assertFalse($bus->resultIsExists('Test'));
        $this->assertTrue($bus->resultIsExists('RequiredActor'));
        $this->assertTrue($bus->resultIsExists('FallbackRequiredActor'));
    }

    #[Test]
    public function run_with_retrying_actor()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doActor(
            new Actor(
                id: 'Test',
                handler: function (): void {},
                required: ['RequiredActor'],
                externalAccess: true,
            ),
        );

        $busBuilder->addActor(
            new Actor(
                id: 'RequiredActor',
                handler: fn() => Result::fail(),
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
                fallbacks: [
                    'FallbackRequiredActor',
                ],
                retries: 2,
            ),
        );

        $busBuilder->addActor(
            new Actor(
                id: 'FallbackRequiredActor',
                handler: fn() => new stdClass(),
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build()->run();

        $this->assertTrue($bus->resultIsExists('Test'));
        $this->assertTrue($bus->resultIsExists('RequiredActor'));
        $this->assertTrue($bus->resultIsExists('FallbackRequiredActor'));
    }
}
