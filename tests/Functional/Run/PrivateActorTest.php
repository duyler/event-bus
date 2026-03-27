<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Exception\CannotRequirePrivateActorException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PrivateActorTest extends TestCase
{
    #[Test]
    public function run_require_private_actor()
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addActor(
            new Actor(
                id: 'PrivateActor',
                handler: function (): void {},
                externalAccess: true,
                private: true,
            ),
        );

        $builder->doActor(
            new Actor(
                id: 'TestActor',
                handler: function (): void {},
                required: ['PrivateActor'],
                externalAccess: true,
            ),
        );

        $builder->addActor(
            new Actor(
                id: 'PrivateWithSealedActor',
                handler: function (): void {},
                externalAccess: true,
                private: true,
                sealed: ['TestActor'],
            ),
        );

        $builder->doActor(
            new Actor(
                id: 'TestWithSealedActor',
                handler: function (): void {},
                required: ['PrivateActor', 'PrivateWithSealedActor'],
                externalAccess: true,
            ),
        );

        $builder->doActor(
            new Actor(
                id: 'SomeActor',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $this->expectException(CannotRequirePrivateActorException::class);

        $builder->build();
    }
}
