<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Trigger;
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

        $this->assertSame($log->actionLog, ['Test']);
        $this->assertSame($log->mainEventLog, ['Test.Success']);
        $this->assertSame($log->eventLog, []);
        $this->assertSame($log->repeatedEventLog, []);
    }

    #[Test]
    public function getLog_with_autoreset()
    {
        $busBuilder = new BusBuilder(new BusConfig(autoreset: true));
        $busBuilder->doAction(new Action(id: 'Test', handler: function (): void {}));
        $bus = $busBuilder->build()->run();
        $log = $bus->getLog();

        $this->assertSame($log->actionLog, ['Test']);
        $this->assertSame($log->mainEventLog, ['Test.Success']);
        $this->assertSame($log->eventLog, []);
        $this->assertSame($log->repeatedEventLog, []);
        $this->assertSame($log->retriesLog, []);
        $this->assertSame($log->successLog, ['Test']);
        $this->assertSame($log->failLog, []);
        $this->assertSame($log->suspendedLog, []);
        $this->assertEquals($log->beginAction, 'Test');
        $this->assertEquals($log->errorAction, null);
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

        $busBuilder->addTrigger(
            new Trigger(
                subjectId: 'Test4',
                actionId: 'Test3',
            ),
        );

        $bus = $busBuilder->build()->run();
        $log = $bus->getLog();

        $this->asserttrue(3 === count($log->mainEventLog));
        $this->asserttrue(1 === count($log->repeatedEventLog));
    }
}
