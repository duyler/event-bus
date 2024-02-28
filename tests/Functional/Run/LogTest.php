<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Action;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    #[Test]
    public function getLog_without_autoreset()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(new Action(id: 'Test', handler: function () {}));
        $bus = $busBuilder->build()->run();
        $bus->reset();
        $log = $bus->getLog();

        $this->assertSame($log->getActionLog(), ['Test']);
        $this->assertSame($log->getMainActionLog(), ['Test.Success']);
        $this->assertSame($log->getTriggerLog(), []);
        $this->assertSame($log->getRepeatedActionLog(), []);
    }

    #[Test]
    public function getLog_with_autoreset()
    {
        $busBuilder = new BusBuilder(new BusConfig(autoreset: true));
        $busBuilder->doAction(new Action(id: 'Test', handler: function () {}));
        $bus = $busBuilder->build()->run();
        $log = $bus->getLog();

        $this->assertSame($log->getActionLog(), ['Test']);
        $this->assertSame($log->getMainActionLog(), ['Test.Success']);
        $this->assertSame($log->getTriggerLog(), []);
        $this->assertSame($log->getRepeatedActionLog(), []);
    }
}
