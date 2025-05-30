<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Build;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Exception\CircularCallActionException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CircularRequiredActionTest extends TestCase
{
    #[Test]
    public function build_with_circular_required_action()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'StartAction',
                handler: function (): void {},
                required: ['RequiredChildren'],
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'RequiredChildren',
                handler: function (): void {},
                required: ['RequiredCircular'],
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'RequiredCircular',
                handler: function (): void {},
                required: ['StartAction'],
            ),
        );

        $this->expectException(CircularCallActionException::class);

        $busBuilder->build();
    }
}
