<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Exception\NotAllowedSealedActorException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SealedActorTest extends TestCase
{
    #[Test]
    public function run_require_accept_actor(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addActor(
            new Actor(
                id: 'SealedActor',
                handler: function (): void {},
                externalAccess: true,
                sealed: ['AcceptActor'],
            ),
        );

        $builder->doActor(
            new Actor(
                id: 'AcceptActor',
                handler: function (): void {},
                required: ['SealedActor'],
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $result = $bus->getResult('SealedActor');
        $this->assertEquals(ResultStatus::Success, $result->status);
    }

    #[Test]
    public function run_require_not_accept_actor(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addActor(
            new Actor(
                id: 'SealedActor',
                handler: function (): void {},
                externalAccess: true,
                sealed: ['SomeActor'],
            ),
        );

        $builder->doActor(
            new Actor(
                id: 'NotAcceptActor',
                handler: function (): void {},
                required: ['SealedActor'],
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

        $this->expectException(NotAllowedSealedActorException::class);

        $builder->build();
    }
}
