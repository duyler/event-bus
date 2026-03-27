<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Test\Functional\State\Support\HandleUnresolvedTaskStateHandler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MainUnresolvedTest extends TestCase
{
    #[Test]
    public function handle_with_skip_unresolved_actor(): void
    {
        $busBuilder = new BusBuilder(new BusConfig(
            allowSkipUnresolvedActors: true,
        ));

        $busBuilder->addStateHandler(new HandleUnresolvedTaskStateHandler());
        $busBuilder->addActor(
            new Actor(
                id: 'Failed',
                handler: fn() => Result::fail(),
            ),
        );
        $busBuilder->doActor(
            new Actor(
                id: 'Unresolved',
                handler: function (): void {},
                required: ['Failed'],
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('Failed'));
        $this->assertTrue($bus->resultIsExists('ActorFromStateHandler'));
        $this->assertFalse($bus->resultIsExists('Unresolved'));
    }
}
