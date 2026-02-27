<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    #[Test]
    public function getLog_without_autoreset()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(new Action(id: 'Test', handler: function (): void {}));
        $bus = $busBuilder->build()->run();
        $bus->reset();
        $log = $bus->getLog();

        $this->assertSame(['Test'], $log->actionLog);
        $this->assertSame(['Test.Success'], $log->mainEventLog);
        $this->assertSame(['Test::Success'], $log->eventLog);
        $this->assertSame([], $log->repeatedEventLog);
    }

    #[Test]
    public function getLog_with_autoreset()
    {
        $busBuilder = new BusBuilder(new BusConfig(autoreset: true));
        $busBuilder->doAction(new Action(id: 'Test', handler: function (): void {}));
        $bus = $busBuilder->build()->run();
        $log = $bus->getLog();
        $this->assertSame(['Test'], $log->actionLog);
        $this->assertSame(['Test.Success'], $log->mainEventLog);
        $this->assertSame(['Test::Success'], $log->eventLog);
        $this->assertSame([], $log->repeatedEventLog);
        $this->assertSame([], $log->retriesLog);
        $this->assertSame(['Test'], $log->successLog);
        $this->assertSame([], $log->failLog);
        $this->assertSame([], $log->suspendedLog);
        $this->assertEquals('Test', $log->beginAction);
        $this->assertEquals(null, $log->errorAction);
    }

    #[Test]
    public function getLog_with_circular_call()
    {
        $busBuilder = new BusBuilder(
            new BusConfig(
                autoreset: true,
                allowCircularCall: true,
                logMaxSize: 3,
            ),
        );
        $busBuilder->doAction(new Action(id: 'Test1', handler: function (): void {}));
        $busBuilder->doAction(new Action(id: 'Test2', handler: function (): void {}));
        $busBuilder->doAction(new Action(id: 'Test3', handler: function (): void {}, repeatable: true));
        $busBuilder->doAction(new Action(id: 'Test4', handler: function (): void {}));

        $bus = $busBuilder->build()->run();
        $log = $bus->getLog();

        $this->asserttrue(3 === count($log->mainEventLog));
        $this->asserttrue(0 === count($log->repeatedEventLog));
    }
}
