<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Build;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Exception\CircularCallActorException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CircularRequiredActorTest extends TestCase
{
    #[Test]
    public function build_with_circular_required_actor()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doActor(
            new Actor(
                id: 'StartActor',
                handler: function (): void {},
                required: ['RequiredChildren'],
            ),
        );

        $busBuilder->addActor(
            new Actor(
                id: 'RequiredChildren',
                handler: function (): void {},
                required: ['RequiredCircular'],
            ),
        );

        $busBuilder->addActor(
            new Actor(
                id: 'RequiredCircular',
                handler: function (): void {},
                required: ['StartActor'],
            ),
        );

        $this->expectException(CircularCallActorException::class);

        $busBuilder->build();
    }
}
