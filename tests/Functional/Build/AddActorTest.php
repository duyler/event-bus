<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Build;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Exception\ActorAlreadyDefinedException;
use Duyler\EventBus\Exception\ActorNotDefinedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AddActorTest extends TestCase
{
    #[Test]
    public function addActor_with_redefine()
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addActor(
            new Actor(
                id: TestActor::TestActor,
                handler: function (): void {},
            ),
        );

        $this->expectException(ActorAlreadyDefinedException::class);

        $builder->addActor(
            new Actor(
                id: TestActor::TestActor,
                handler: function (): void {},
            ),
        );
    }

    #[Test]
    public function doActor_with_redefine()
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->doActor(
            new Actor(
                id: 'Test',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $this->expectException(ActorAlreadyDefinedException::class);

        $builder->doActor(
            new Actor(
                id: 'Test',
                handler: function (): void {},
                externalAccess: true,
            ),
        );
    }

    #[Test]
    public function doActor_with_undefined()
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->doActor(
            new Actor(
                id: 'Test',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $builder->doActor(
            new Actor(
                id: 'TestWithUndefinedRequire',
                handler: function (): void {},
                required: ['UndefinedRequire'],
                externalAccess: true,
            ),
        );

        $this->expectException(ActorNotDefinedException::class);

        $builder->build()->run();
    }

    #[Test]
    public function actorIsExists_with_actor()
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->doActor(
            new Actor(
                id: 'Test',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $this->assertTrue($builder->actorIsExists('Test'));
    }
}

enum TestActor
{
    case TestActor;
}
