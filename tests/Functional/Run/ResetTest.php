<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ResetTest extends TestCase
{
    #[Test]
    public function run_with_selective_reset(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();
        $this->assertTrue($bus->resultIsExists('Test'));
        $bus->reset();
        $this->assertFalse($bus->resultIsExists('Test'));
    }
}
