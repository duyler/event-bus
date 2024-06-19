<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Functional\Run;

use Duyler\ActionBus\Build\Action;
use Duyler\ActionBus\BusBuilder;
use Duyler\ActionBus\BusConfig;
use Duyler\ActionBus\Enum\ResetMode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ResetTest extends TestCase
{
    #[Test]
    public function run_with_soft_reset(): void
    {
        $busBuilder = new BusBuilder(new BusConfig(resetMode: ResetMode::Full));

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

    #[Test]
    public function run_with_selective_reset(): void
    {
        $busBuilder = new BusBuilder(new BusConfig(resetMode: ResetMode::Selective));

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
