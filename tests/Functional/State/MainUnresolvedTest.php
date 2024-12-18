<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Test\Functional\State\Support\HandleUnresolvedTaskStateHandler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MainUnresolvedTest extends TestCase
{
    #[Test]
    public function handle_with_skip_unresolved_action(): void
    {
        $busBuilder = new BusBuilder(new BusConfig(
            allowSkipUnresolvedActions: true,
        ));

        $busBuilder->addStateHandler(new HandleUnresolvedTaskStateHandler());
        $busBuilder->addAction(
            new Action(
                id: 'Failed',
                handler: fn() => Result::fail(),
            ),
        );
        $busBuilder->doAction(
            new Action(
                id: 'Unresolved',
                handler: function () {},
                required: ['Failed'],
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('Failed'));
        $this->assertTrue($bus->resultIsExists('ActionFromStateHandler'));
        $this->assertFalse($bus->resultIsExists('Unresolved'));
    }
}
