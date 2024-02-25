<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Trigger;
use Duyler\EventBus\Enum\ResultStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class TriggerTest extends TestCase
{
    #[Test]
    public function run_without_contract(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addAction(
            new Action(
                id: 'ForTriggerAction',
                handler: function () {},
                triggeredOn: 'TestTrigger',
                externalAccess: true,
            )
        );

        $bus = $builder->build();
        $bus->dispatchTrigger(
            new Trigger(
                id: 'TestTrigger',
                status: ResultStatus::Success,
            )
        );

        $bus->run();

        $this->assertTrue($bus->resultIsExists('ForTriggerAction'));
        $this->assertTrue($bus->resultIsExists('TestTrigger'));
    }

    #[Test]
    public function run_with_contract(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addAction(
            new Action(
                id: 'ForTriggerAction',
                handler: function (stdClass $data) {},
                triggeredOn: 'TestTrigger',
                argument: stdClass::class,
                externalAccess: true
            )
        );

        $bus = $builder->build();
        $bus->dispatchTrigger(
            new Trigger(
                id: 'TestTrigger',
                status: ResultStatus::Success,
                data: new stdClass(),
                contract: stdClass::class,
            )
        );

        $bus->run();

        $this->assertTrue($bus->resultIsExists('ForTriggerAction'));
        $this->assertTrue($bus->resultIsExists('TestTrigger'));
        $this->assertInstanceOf(stdClass::class, $bus->getResult('TestTrigger')->data);
    }
}
