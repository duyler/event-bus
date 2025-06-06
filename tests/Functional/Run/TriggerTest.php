<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Trigger;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TriggerTest extends TestCase
{
    #[Test]
    public function run_action_with_trigger(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'ActionFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'SubscribedActionFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $busBuilder->addTrigger(
            new Trigger(
                subjectId: 'ActionFromBuilder',
                actionId: 'SubscribedActionFromBuilder',
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('ActionFromBuilder'));
        $this->assertTrue($bus->resultIsExists('SubscribedActionFromBuilder'));
    }
}
