<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Enum\ResetMode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ResetTest extends TestCase
{
    #[Test]
    public function run_with_soft_reset(): void
    {
        $busBuilder = new BusBuilder(new BusConfig(resetMode: ResetMode::Soft));

        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: function (): void {},
                externalAccess: true,
            )
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
            )
        );

        $bus = $busBuilder->build();
        $bus->run();
        $this->assertTrue($bus->resultIsExists('Test'));
        $bus->reset();
        $this->assertFalse($bus->resultIsExists('Test'));
    }
}
