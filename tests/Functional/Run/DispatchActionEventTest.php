<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Action\ActionEventDispatcher;
use Duyler\EventBus\Action\Context\ActionContext;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\Event;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Exception\ContractForDataNotReceivedException;
use Duyler\EventBus\Exception\DataForContractNotReceivedException;
use Duyler\EventBus\Exception\DataMustBeCompatibleWithContractException;
use Duyler\EventBus\Formatter\IdFormatter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final readonly class TestEventData
{
    public function __construct(
        public string $value = '',
    ) {}
}

final readonly class TestEventData2
{
    public function __construct(
        public int $count = 0,
    ) {}
}

class DispatchActionEventTest extends TestCase
{
    #[Test]
    public function dispatch_action_event_with_data(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new Event(id: 'DynamicEvent', type: TestEventData::class));

        $builder->addAction(
            new Action(
                id: 'SubscriberAction',
                handler: function (ActionContext $context): void {},
                onOne: 'DynamicEvent' . IdFormatter::DELIMITER . 'Success',
                argument: TestEventData::class,
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'PublisherAction',
                handler: function (ActionContext $context): void {
                    $context->call(
                        function (ActionEventDispatcher $dispatcher): void {
                            $dispatcher->dispatchActionEvent(
                                eventId: 'DynamicEvent' . IdFormatter::DELIMITER . 'Success',
                                data: new TestEventData(),
                                eventDefinition: Event::success('DynamicEvent', TestEventData::class),
                            );
                        },
                    );
                },
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('PublisherAction'), 'PublisherAction should exist');
        $this->assertTrue($bus->resultIsExists('SubscriberAction'), 'SubscriberAction should exist');
        $this->assertTrue($bus->resultIsExists('DynamicEvent' . IdFormatter::DELIMITER . 'Success'));
    }

    #[Test]
    public function dispatch_action_event_without_data(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(Event::fail(id: 'DynamicEvent'));

        $builder->addAction(
            new Action(
                id: 'SubscriberAction',
                handler: function (ActionContext $context): void {},
                onOne: 'DynamicEvent' . IdFormatter::DELIMITER . 'Fail',
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'PublisherAction',
                handler: function (ActionContext $context): void {
                    $context->call(
                        function (ActionEventDispatcher $dispatcher): void {
                            $dispatcher->dispatchActionEvent(
                                eventId: 'DynamicEvent' . IdFormatter::DELIMITER . 'Fail',
                                data: null,
                                eventDefinition: Event::fail('DynamicEvent'),
                            );
                        },
                    );
                },
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('PublisherAction'));
        $this->assertTrue($bus->resultIsExists('SubscriberAction'));
        $this->assertTrue($bus->resultIsExists('DynamicEvent' . IdFormatter::DELIMITER . 'Fail'));
    }

    #[Test]
    public function dispatch_action_event_without_subscribers(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->doAction(
            new Action(
                id: 'PublisherAction',
                handler: function (ActionContext $context): void {
                    $context->call(
                        function (ActionEventDispatcher $dispatcher): void {
                            $dispatcher->dispatchActionEvent(
                                eventId: 'DynamicEvent' . IdFormatter::DELIMITER . 'Success',
                                data: new TestEventData(),
                                eventDefinition: Event::success('DynamicEvent', TestEventData::class),
                            );
                        },
                    );
                },
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('PublisherAction'));
        $this->assertFalse($bus->resultIsExists('DynamicEvent' . IdFormatter::DELIMITER . 'Success'));
    }

    #[Test]
    public function dispatch_action_event_with_multiple_subscribers(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new Event(id: 'DynamicEvent', type: TestEventData::class));

        $builder->addAction(
            new Action(
                id: 'SubscriberAction1',
                handler: function (ActionContext $context): void {},
                onOne: 'DynamicEvent' . IdFormatter::DELIMITER . 'Success',
                argument: TestEventData::class,
                externalAccess: true,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'SubscriberAction2',
                handler: function (ActionContext $context): void {},
                onOne: 'DynamicEvent' . IdFormatter::DELIMITER . 'Success',
                argument: TestEventData::class,
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'PublisherAction',
                handler: function (ActionContext $context): void {
                    $context->call(
                        function (ActionEventDispatcher $dispatcher): void {
                            $dispatcher->dispatchActionEvent(
                                eventId: 'DynamicEvent' . IdFormatter::DELIMITER . 'Success',
                                data: new TestEventData(),
                                eventDefinition: Event::success('DynamicEvent', TestEventData::class),
                            );
                        },
                    );
                },
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('PublisherAction'));
        $this->assertTrue($bus->resultIsExists('SubscriberAction1'));
        $this->assertTrue($bus->resultIsExists('SubscriberAction2'));
        $this->assertTrue($bus->resultIsExists('DynamicEvent' . IdFormatter::DELIMITER . 'Success'));
    }

    #[Test]
    public function dispatch_action_event_data_passed_to_subscriber(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new Event(id: 'DynamicEvent', type: TestEventData::class));

        $builder->addAction(
            new Action(
                id: 'SubscriberAction',
                handler: fn(ActionContext $context) => $context->argument(),
                onOne: 'DynamicEvent' . IdFormatter::DELIMITER . 'Success',
                argument: TestEventData::class,
                type: TestEventData::class,
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'PublisherAction',
                handler: function (ActionContext $context): void {
                    $context->call(
                        function (ActionEventDispatcher $dispatcher): void {
                            $dispatcher->dispatchActionEvent(
                                eventId: 'DynamicEvent' . IdFormatter::DELIMITER . 'Success',
                                data: new TestEventData(value: 'test-value'),
                                eventDefinition: Event::success('DynamicEvent', TestEventData::class),
                            );
                        },
                    );
                },
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('SubscriberAction'));
        $subscriberResult = $bus->getResult('SubscriberAction');
        $this->assertInstanceOf(TestEventData::class, $subscriberResult->data);
        $this->assertSame('test-value', $subscriberResult->data->value);
    }

    #[Test]
    public function dispatch_action_event_with_data_but_no_contract_throws(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->doAction(
            new Action(
                id: 'PublisherAction',
                handler: function (ActionContext $context): void {
                    $context->call(
                        function (ActionEventDispatcher $dispatcher): void {
                            $dispatcher->dispatchActionEvent(
                                eventId: 'DynamicEvent' . IdFormatter::DELIMITER . 'Success',
                                data: new TestEventData(),
                                eventDefinition: Event::success('DynamicEvent'),
                            );
                        },
                    );
                },
                externalAccess: true,
            ),
        );

        $bus = $builder->build();

        $this->expectException(ContractForDataNotReceivedException::class);

        $bus->run();
    }

    #[Test]
    public function dispatch_action_event_with_invalid_data_for_contract_throws(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->doAction(
            new Action(
                id: 'PublisherAction',
                handler: function (ActionContext $context): void {
                    $context->call(
                        function (ActionEventDispatcher $dispatcher): void {
                            $dispatcher->dispatchActionEvent(
                                eventId: 'DynamicEvent' . IdFormatter::DELIMITER . 'Success',
                                data: new TestEventData2(),
                                eventDefinition: Event::success('DynamicEvent', TestEventData::class),
                            );
                        },
                    );
                },
                externalAccess: true,
            ),
        );

        $bus = $builder->build();

        $this->expectException(DataMustBeCompatibleWithContractException::class);

        $bus->run();
    }

    #[Test]
    public function dispatch_action_event_with_contract_but_no_data_throws(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->doAction(
            new Action(
                id: 'PublisherAction',
                handler: function (ActionContext $context): void {
                    $context->call(
                        function (ActionEventDispatcher $dispatcher): void {
                            $dispatcher->dispatchActionEvent(
                                eventId: 'DynamicEvent' . IdFormatter::DELIMITER . 'Success',
                                data: null,
                                eventDefinition: Event::success('DynamicEvent', TestEventData::class),
                            );
                        },
                    );
                },
                externalAccess: true,
            ),
        );

        $bus = $builder->build();

        $this->expectException(DataForContractNotReceivedException::class);

        $bus->run();
    }

    #[Test]
    public function dispatch_action_event_data_passed_via_on_any(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new Event(id: 'Event1', type: TestEventData::class));

        $builder->addAction(
            new Action(
                id: 'SubscriberAction',
                handler: fn(ActionContext $context) => $context->argument(),
                onAny: [
                    'Event1' . IdFormatter::DELIMITER . 'Success',
                ],
                argument: TestEventData::class,
                type: TestEventData::class,
                externalAccess: true,
            ),
        );

        $bus = $builder->build();

        $bus->dispatchEvent(new \Duyler\EventBus\Dto\Event(
            id: 'Event1' . IdFormatter::DELIMITER . 'Success',
            data: new TestEventData(value: 'from-on-any'),
        ));

        $bus->run();

        $this->assertTrue($bus->resultIsExists('SubscriberAction'));
        $subscriberResult = $bus->getResult('SubscriberAction');
        $this->assertInstanceOf(TestEventData::class, $subscriberResult->data);
        $this->assertSame('from-on-any', $subscriberResult->data->value);
    }

    #[Test]
    public function dispatch_action_event_data_passed_via_on_all(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new Event(id: 'Event1', type: TestEventData::class));
        $builder->addEvent(new Event(id: 'Event2', type: TestEventData2::class));

        $builder->addAction(
            new Action(
                id: 'SubscriberAction',
                handler: function (ActionContext $context): void {},
                onAll: [
                    'Event1' . IdFormatter::DELIMITER . 'Success',
                    'Event2' . IdFormatter::DELIMITER . 'Success',
                ],
                externalAccess: true,
            ),
        );

        $bus = $builder->build();

        $bus->dispatchEvent(new \Duyler\EventBus\Dto\Event(
            id: 'Event1' . IdFormatter::DELIMITER . 'Success',
            data: new TestEventData(value: 'event1-data'),
        ));

        $bus->dispatchEvent(new \Duyler\EventBus\Dto\Event(
            id: 'Event2' . IdFormatter::DELIMITER . 'Success',
            data: new TestEventData2(count: 42),
        ));

        $bus->run();

        $this->assertTrue($bus->resultIsExists('SubscriberAction'));
        $this->assertTrue($bus->resultIsExists('Event1' . IdFormatter::DELIMITER . 'Success'));
        $this->assertTrue($bus->resultIsExists('Event2' . IdFormatter::DELIMITER . 'Success'));
    }
}
