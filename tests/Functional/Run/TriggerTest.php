<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Trigger;
use Duyler\EventBus\Exception\ContractForDataNotReceivedException;
use Duyler\EventBus\Exception\DataForContractNotReceivedException;
use Duyler\EventBus\Exception\DataMustBeCompatibleWithContractException;
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
                data: new stdClass(),
                contract: stdClass::class,
            )
        );

        $bus->run();

        $this->assertTrue($bus->resultIsExists('ForTriggerAction'));
        $this->assertTrue($bus->resultIsExists('TestTrigger'));
        $this->assertInstanceOf(stdClass::class, $bus->getResult('TestTrigger')->data);
    }

    #[Test]
    public function run_with_contract_and_required_triggered_action(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addAction(
            new Action(
                id: 'ForTriggerAction',
                handler: fn(stdClass $data) => $data,
                triggeredOn: 'TestTrigger',
                argument: stdClass::class,
                contract: stdClass::class,
                externalAccess: true,
            )
        );

        $builder->doAction(
            new Action(
                id: 'RequiredTriggered',
                handler: function (stdClass $data) {},
                required: ['ForTriggerAction'],
                argument: stdClass::class,
                externalAccess: true,
            )
        );

        $bus = $builder->build();
        $bus->dispatchTrigger(
            new Trigger(
                id: 'TestTrigger',
                data: new stdClass(),
                contract: stdClass::class,
            )
        );

        $bus->run();

        $this->assertTrue($bus->resultIsExists('ForTriggerAction'));
        $this->assertTrue($bus->resultIsExists('TestTrigger'));
        $this->assertTrue($bus->resultIsExists('RequiredTriggered'));
        $this->assertInstanceOf(stdClass::class, $bus->getResult('TestTrigger')->data);
        $this->assertNull($bus->getResult('RequiredTriggered')->data);
        $this->assertInstanceOf(stdClass::class, $bus->getResult('ForTriggerAction')->data);
    }

    #[Test]
    public function run_with_contract_and_required_triggered_action_without_trigger(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addAction(
            new Action(
                id: 'ForTriggerAction',
                handler: fn(stdClass $data) => $data,
                triggeredOn: 'TestTrigger',
                argument: stdClass::class,
                contract: stdClass::class,
                externalAccess: true,
            )
        );

        $builder->doAction(
            new Action(
                id: 'SomeAction',
                handler: function () {},
                externalAccess: true,
            )
        );

        $builder->doAction(
            new Action(
                id: 'RequiredTriggered',
                handler: function (stdClass $data) {},
                required: ['ForTriggerAction'],
                argument: stdClass::class,
                externalAccess: true,
            )
        );

        $bus = $builder->build();

        $bus->run();

        $this->assertFalse($bus->resultIsExists('ForTriggerAction'));
        $this->assertFalse($bus->resultIsExists('TestTrigger'));
        $this->assertFalse($bus->resultIsExists('RequiredTriggered'));
    }

    #[Test]
    public function run_with_contract_and_without_data(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addAction(
            new Action(
                id: 'ForTriggerAction',
                handler: function () {},
                triggeredOn: 'TestTrigger',
                argument: stdClass::class,
                externalAccess: true,
            )
        );

        $bus = $builder->build();

        $this->expectException(DataForContractNotReceivedException::class);

        $bus->dispatchTrigger(
            new Trigger(
                id: 'TestTrigger',
                contract: stdClass::class,
            )
        );
    }

    #[Test]
    public function run_with_data_and_without_contract(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addAction(
            new Action(
                id: 'ForTriggerAction',
                handler: function () {},
                triggeredOn: 'TestTrigger',
                argument: stdClass::class,
                externalAccess: true,
            )
        );

        $bus = $builder->build();

        $this->expectException(ContractForDataNotReceivedException::class);

        $bus->dispatchTrigger(
            new Trigger(
                id: 'TestTrigger',
                data: new stdClass(),
            )
        );
    }

    #[Test]
    public function run_with_invalid_data_for_contract(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addAction(
            new Action(
                id: 'ForTriggerAction',
                handler: function () {},
                triggeredOn: 'TestTrigger',
                argument: stdClass::class,
                externalAccess: true,
            )
        );

        $bus = $builder->build();

        $this->expectException(DataMustBeCompatibleWithContractException::class);

        $bus->dispatchTrigger(
            new Trigger(
                id: 'TestTrigger',
                data: new stdClass(),
                contract: 'ClassName',
            )
        );
    }
}
