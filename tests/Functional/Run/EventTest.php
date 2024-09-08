<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Event;
use Duyler\EventBus\Exception\ContractForDataNotReceivedException;
use Duyler\EventBus\Exception\DataForContractNotReceivedException;
use Duyler\EventBus\Exception\DataMustBeCompatibleWithContractException;
use Duyler\EventBus\Exception\EventHandlersNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class EventTest extends TestCase
{
    #[Test]
    public function run_without_contract(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addAction(
            new Action(
                id: 'ForEventAction',
                handler: function () {},
                listen: 'TestEvent',
                externalAccess: true,
            ),
        );

        $builder->addEvent(new \Duyler\EventBus\Build\Event(id: 'TestEvent'));

        $bus = $builder->build();
        $bus->dispatchEvent(
            new Event(
                id: 'TestEvent',
            ),
        );

        $bus->run();

        $this->assertTrue($bus->resultIsExists('ForEventAction'));
        $this->assertTrue($bus->resultIsExists('TestEvent'));
    }

    #[Test]
    public function run_with_contract(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addAction(
            new Action(
                id: 'ForEventAction',
                handler: function (stdClass $data) {},
                listen: 'TestEvent',
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $builder->addEvent(new \Duyler\EventBus\Build\Event(id: 'TestEvent', contract: stdClass::class));

        $bus = $builder->build();
        $bus->dispatchEvent(
            new Event(
                id: 'TestEvent',
                data: new stdClass(),
            ),
        );

        $bus->run();

        $this->assertTrue($bus->resultIsExists('ForEventAction'));
        $this->assertTrue($bus->resultIsExists('TestEvent'));
        $this->assertInstanceOf(stdClass::class, $bus->getResult('TestEvent')->data);
    }

    #[Test]
    public function run_with_contract_and_required_event_action(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addAction(
            new Action(
                id: 'ForEventAction',
                handler: fn(stdClass $data) => $data,
                listen: 'TestEvent',
                argument: stdClass::class,
                contract: stdClass::class,
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'RequiredListening',
                handler: function (stdClass $data) {},
                required: ['ForEventAction'],
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $builder->addEvent(new \Duyler\EventBus\Build\Event(id: 'TestEvent', contract: stdClass::class));

        $bus = $builder->build();
        $bus->dispatchEvent(
            new Event(
                id: 'TestEvent',
                data: new stdClass(),
            ),
        );

        $bus->run();

        $this->assertTrue($bus->resultIsExists('ForEventAction'));
        $this->assertTrue($bus->resultIsExists('TestEvent'));
        $this->assertTrue($bus->resultIsExists('RequiredListening'));
        $this->assertInstanceOf(stdClass::class, $bus->getResult('TestEvent')->data);
        $this->assertNull($bus->getResult('RequiredListening')->data);
        $this->assertInstanceOf(stdClass::class, $bus->getResult('ForEventAction')->data);
    }

    #[Test]
    public function run_with_contract_and_required_event_action_without_dispatch_event(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addAction(
            new Action(
                id: 'ForEventAction',
                handler: fn(stdClass $data) => $data,
                listen: 'TestEvent',
                argument: stdClass::class,
                contract: stdClass::class,
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'SomeAction',
                handler: function () {},
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'RequiredListening',
                handler: function (stdClass $data) {},
                required: ['ForEventAction'],
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $builder->addEvent(new \Duyler\EventBus\Build\Event(id: 'TestEvent', contract: stdClass::class));

        $bus = $builder->build();

        $bus->run();

        $this->assertFalse($bus->resultIsExists('ForEventAction'));
        $this->assertFalse($bus->resultIsExists('TestEvent'));
        $this->assertFalse($bus->resultIsExists('RequiredTriggered'));
    }

    #[Test]
    public function run_with_contract_and_without_data(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addAction(
            new Action(
                id: 'ForEventAction',
                handler: function () {},
                listen: 'TestEvent',
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $builder->addEvent(new \Duyler\EventBus\Build\Event(id: 'TestEvent', contract: stdClass::class));

        $bus = $builder->build();

        $this->expectException(DataForContractNotReceivedException::class);

        $bus->dispatchEvent(
            new Event(
                id: 'TestEvent',
            ),
        );
    }

    #[Test]
    public function run_with_data_and_without_contract(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addAction(
            new Action(
                id: 'ForEventAction',
                handler: function () {},
                listen: 'TestEvent',
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $builder->addEvent(new \Duyler\EventBus\Build\Event(id: 'TestEvent'));

        $bus = $builder->build();

        $this->expectException(ContractForDataNotReceivedException::class);

        $bus->dispatchEvent(
            new Event(
                id: 'TestEvent',
                data: new stdClass(),
            ),
        );
    }

    #[Test]
    public function run_with_invalid_data_for_contract(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addAction(
            new Action(
                id: 'ForEventAction',
                handler: function () {},
                listen: 'TestEvent',
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $builder->addEvent(new \Duyler\EventBus\Build\Event(id: 'TestEvent', contract: stdClass::class));

        $bus = $builder->build();

        $this->expectException(DataMustBeCompatibleWithContractException::class);

        $bus->dispatchEvent(
            new Event(
                id: 'TestEvent',
                data: new class {},
            ),
        );
    }

    #[Test]
    public function run_with_not_found_event_handler(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new \Duyler\EventBus\Build\Event(id: 'TestEvent'));

        $bus = $builder->build();

        $this->expectException(EventHandlersNotFoundException::class);

        $bus->dispatchEvent(
            new Event(
                id: 'TestEvent',
            ),
        );
    }
}
