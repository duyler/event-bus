<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Functional\Run;

use Duyler\ActionBus\Build\Action;
use Duyler\ActionBus\Build\Subscription;
use Duyler\ActionBus\BusBuilder;
use Duyler\ActionBus\BusConfig;
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
        $this->assertSame($log->getEventLog(), []);
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
        $this->assertSame($log->getEventLog(), []);
        $this->assertSame($log->getRepeatedActionLog(), []);
        $this->assertSame($log->getRetriesLog(), []);
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
        $busBuilder->doAction(new Action(id: 'Test1', handler: function () {}));
        $busBuilder->doAction(new Action(id: 'Test2', handler: function () {}));
        $busBuilder->doAction(new Action(id: 'Test3', handler: function () {}, repeatable: true));
        $busBuilder->doAction(new Action(id: 'Test4', handler: function () {}));

        $busBuilder->addSubscription(
            new Subscription(
                subjectId: 'Test4',
                actionId: 'Test3',
            ),
        );

        $bus = $busBuilder->build()->run();
        $log = $bus->getLog();

        $this->asserttrue(3 === count($log->getActionLog()));
        $this->asserttrue(1 === count($log->getRepeatedActionLog()));
    }
}
