<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Action\Context\ActionContext;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Event;
use Duyler\EventBus\Event\EventDispatcher;
use Duyler\EventBus\Exception\ContractForDataNotReceivedException;
use Duyler\EventBus\Exception\DataForContractNotReceivedException;
use Duyler\EventBus\Exception\DataMustBeCompatibleWithContractException;
use Duyler\EventBus\Exception\DispatchedEventNotDefinedException;
use Duyler\EventBus\Exception\EventNotDefinedException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
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
                handler: function (): void {},
                listen: ['TestEvent'],
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
    public function run_without_dispatch_event(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->doAction(
            new Action(
                id: 'ForEventAction',
                handler: function (): void {},
                listen: ['TestEvent'],
                externalAccess: true,
            ),
        );

        $builder->addEvent(new \Duyler\EventBus\Build\Event(id: 'TestEvent'));

        $bus = $builder->build();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('TaskQueue is empty');

        $bus->run();
    }

    #[Test]
    public function run_with_not_all_condition(): void
    {
        $builder = new BusBuilder(new BusConfig(allowSkipUnresolvedActions: false));
        $builder->doAction(
            new Action(
                id: 'ForEventAction',
                handler: function (): void {},
                listen: ['TestEvent1', 'TestEvent2'],
                externalAccess: true,
            ),
        );

        $builder->addEvent(new \Duyler\EventBus\Build\Event(id: 'TestEvent1'));
        $builder->addEvent(new \Duyler\EventBus\Build\Event(id: 'TestEvent2'));

        $bus = $builder->build();

        $bus->dispatchEvent(
            new Event(
                id: 'TestEvent1',
            ),
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('TaskQueue is empty');

        $bus->run();
    }

    #[Test]
    public function run_with_contract(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addAction(
            new Action(
                id: 'ForEventAction',
                handler: function (ActionContext $context): void {},
                listen: ['TestEvent'],
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $builder->addEvent(new \Duyler\EventBus\Build\Event(id: 'TestEvent', type: stdClass::class, immutable: false));

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
                handler: fn(ActionContext $context) => $context->argument(),
                listen: ['TestEvent'],
                argument: stdClass::class,
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'RequiredListening',
                handler: function (): void {},
                required: ['ForEventAction'],
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $builder->addEvent(new \Duyler\EventBus\Build\Event(id: 'TestEvent', type: stdClass::class, immutable: false));

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
                listen: ['TestEvent'],
                argument: stdClass::class,
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'SomeAction',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'RequiredListening',
                handler: function (stdClass $data): void {},
                required: ['ForEventAction'],
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $builder->addEvent(new \Duyler\EventBus\Build\Event(id: 'TestEvent', type: stdClass::class, immutable: false));

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
                handler: function (): void {},
                listen: ['TestEvent'],
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $builder->addEvent(new \Duyler\EventBus\Build\Event(id: 'TestEvent', type: stdClass::class, immutable: false));

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
                handler: function (): void {},
                listen: ['TestEvent'],
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
                handler: function (): void {},
                listen: ['TestEvent'],
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $builder->addEvent(new \Duyler\EventBus\Build\Event(id: 'TestEvent', type: stdClass::class, immutable: false));

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
    public function run_with_event_not_defined(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->doAction(
            new Action(
                id: 'ForEventAction',
                handler: function (): void {},
                listen: ['TestEvent'],
                argument: stdClass::class,
                externalAccess: true,
            ),
        );

        $this->expectException(EventNotDefinedException::class);

        $builder->build();
    }

    #[Test]
    public function run_with_dispatch_event_from_action(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->doAction(
            new Action(
                id: 'ForEventAction',
                handler: function (): void {
                    EventDispatcher::dispatch(new Event(
                        id: 'TestEvent1',
                    ));
                },
            ),
        );

        $builder->addAction(
            new Action(
                id: 'ForEventListenAction1',
                handler: function (ActionContext $context): void {
                    $context->call(
                        function (EventDispatcherInterface $dispatcher): void {
                            $dispatcher->dispatch(new Event(
                                id: 'TestEvent2',
                            ));
                        },
                    );
                },
                listen: ['TestEvent1'],
            ),
        );

        $builder->addEvent(new \Duyler\EventBus\Build\Event(id: 'TestEvent1'));
        $builder->addEvent(new \Duyler\EventBus\Build\Event(id: 'TestEvent2'));

        $bus = $builder->build();

        $bus->run();

        $this->assertTrue($bus->resultIsExists('ForEventAction'));
        $this->assertTrue($bus->resultIsExists('TestEvent1'));
        $this->assertFalse($bus->resultIsExists('TestEvent2'));
        $this->assertTrue($bus->resultIsExists('ForEventListenAction1'));
    }

    #[Test]
    public function run_with_dispatch_not_defined_event_from_action(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->doAction(
            new Action(
                id: 'ForEventAction',
                handler: function (): void {
                    EventDispatcher::dispatch(new Event(
                        id: 'TestNotDefinedEvent',
                    ));
                },
            ),
        );

        $bus = $builder->build();

        $this->expectException(DispatchedEventNotDefinedException::class);

        $bus->run();
    }

    #[Test]
    public function run_with_dispatch_invalid_event_from_action(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->doAction(
            new Action(
                id: 'ForEventAction',
                handler: function (ActionContext $context): void {
                    $context->call(
                        function (EventDispatcherInterface $dispatcher): void {
                            $dispatcher->dispatch(new stdClass());
                        },
                    );
                },
            ),
        );

        $bus = $builder->build();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Event must be an instance of ' . Event::class);

        $bus->run();
    }
}
