<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\integration\Bus;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusInterface;
use Duyler\EventBus\Dto\Config;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Throwable;

class BuildBusTest extends TestCase
{
    #[Test]
    public function run_empty_bus(): void
    {
        $bus = new BusBuilder(new Config());
        $bus = $bus->build();

        $this->assertInstanceOf(BusInterface::class, $bus);

        $this->expectException(Throwable::class);
        $bus->run();
    }

    protected function setUp(): void
    {
        parent::setUp();
    }
}
