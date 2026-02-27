<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Action\Context\ActionContext;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\Event as BuildEvent;
use Duyler\EventBus\Build\Id;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Event;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Exception\CannotSubscribeOnSilentActionException;
use Duyler\EventBus\Formatter\IdFormatter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final readonly class OnOneTestDTO
{
    public function __construct(
        public string $orderId = '',
    ) {}
}

class OnOneSubscriptionTest extends TestCase
{
    #[Test]
    public function action_subscribes_to_another_action_with_success(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::success('CreateOrder', OnOneTestDTO::class));

        $builder->addAction(
            new Action(
                id: 'SendNotification',
                handler: fn() => new OnOneTestDTO(),
                onOne: Id::success('CreateOrder'),
                type: OnOneTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'CreateOrder',
                handler: fn() => new OnOneTestDTO(orderId: 'test'),
                type: OnOneTestDTO::class,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('CreateOrder'));
        $this->assertTrue($bus->resultIsExists('SendNotification'));
    }

    #[Test]
    public function action_subscribes_to_fail_status(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::fail('CreateOrder'));

        $builder->addAction(
            new Action(
                id: 'HandleFailure',
                handler: fn() => Result::fail(),
                onOne: Id::fail('CreateOrder'),
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'CreateOrder',
                handler: fn() => Result::fail(),
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('CreateOrder'));
        $this->assertTrue($bus->resultIsExists('HandleFailure'));
    }

    #[Test]
    public function action_does_not_run_on_wrong_status(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::success('CreateOrder', OnOneTestDTO::class));
        $builder->addEvent(BuildEvent::fail('CreateOrder'));

        $builder->addAction(
            new Action(
                id: 'HandleFailure',
                handler: fn() => Result::fail(),
                onOne: Id::fail('CreateOrder'),
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'CreateOrder',
                handler: fn() => new OnOneTestDTO(orderId: 'test'),
                type: OnOneTestDTO::class,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('CreateOrder'));
        $this->assertFalse($bus->resultIsExists('HandleFailure'));
    }

    #[Test]
    public function data_passed_from_action_event_to_subscriber(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::success('CreateOrder', OnOneTestDTO::class));

        $builder->addAction(
            new Action(
                id: 'SendNotification',
                handler: fn(ActionContext $context) => $context->argument(),
                onOne: Id::success('CreateOrder'),
                argument: OnOneTestDTO::class,
                type: OnOneTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'CreateOrder',
                handler: fn() => new OnOneTestDTO(orderId: 'test'),
                type: OnOneTestDTO::class,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('CreateOrder'));
        $this->assertTrue($bus->resultIsExists('SendNotification'));

        $result = $bus->getResult('SendNotification');
        $this->assertInstanceOf(OnOneTestDTO::class, $result->data);
    }

    #[Test]
    public function silent_action_does_not_generate_event(): void
    {
        $this->expectException(CannotSubscribeOnSilentActionException::class);

        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::success('SilentAction', OnOneTestDTO::class));

        $builder->addAction(
            new Action(
                id: 'SubscriberAction',
                handler: fn() => new OnOneTestDTO(),
                onOne: Id::success('SilentAction'),
                type: OnOneTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'SilentAction',
                handler: fn() => new OnOneTestDTO(),
                type: OnOneTestDTO::class,
                silent: true,
            ),
        );

        $builder->build();
    }

    #[Test]
    public function external_event_with_status_activates_subscriber(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::success('OrderCreated', OnOneTestDTO::class));

        $builder->addAction(
            new Action(
                id: 'ProcessOrder',
                handler: fn(ActionContext $context) => $context->argument(),
                onOne: Id::success('OrderCreated'),
                argument: OnOneTestDTO::class,
                type: OnOneTestDTO::class,
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->dispatchEvent(new Event(
            id: 'OrderCreated',
            data: new OnOneTestDTO(orderId: 'order-123'),
        ));
        $bus->run();

        $this->assertTrue($bus->resultIsExists('ProcessOrder'));
        $this->assertTrue($bus->resultIsExists('OrderCreated' . IdFormatter::DELIMITER . 'Success'));

        $result = $bus->getResult('ProcessOrder');
        $this->assertInstanceOf(OnOneTestDTO::class, $result->data);
        $this->assertSame('order-123', $result->data->orderId);
    }

    #[Test]
    public function external_fail_event_activates_subscriber(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::fail('OrderFailed'));

        $builder->addAction(
            new Action(
                id: 'HandleFailedOrder',
                handler: fn() => Result::fail(),
                onOne: Id::fail('OrderFailed'),
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->dispatchEvent(new Event(
            id: 'OrderFailed',
            status: ResultStatus::Fail,
        ));
        $bus->run();

        $this->assertTrue($bus->resultIsExists('HandleFailedOrder'));
        $this->assertTrue($bus->resultIsExists('OrderFailed' . IdFormatter::DELIMITER . 'Fail'));
    }

    #[Test]
    public function multiple_subscribers_on_same_event(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::success('OrderCreated'));

        $builder->addAction(
            new Action(
                id: 'SendEmailNotification',
                handler: fn() => Result::fail(),
                onOne: Id::success('OrderCreated'),
                externalAccess: true,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'SendSmsNotification',
                handler: fn() => Result::fail(),
                onOne: Id::success('OrderCreated'),
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->dispatchEvent(new Event(
            id: 'OrderCreated',
        ));
        $bus->run();

        $this->assertTrue($bus->resultIsExists('SendEmailNotification'));
        $this->assertTrue($bus->resultIsExists('SendSmsNotification'));
    }

    #[Test]
    public function chain_of_actions_via_on_one(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::success('Step1', OnOneTestDTO::class));
        $builder->addEvent(BuildEvent::success('Step2', OnOneTestDTO::class));

        $builder->addAction(
            new Action(
                id: 'Step2',
                handler: fn() => new OnOneTestDTO(orderId: 'step2'),
                onOne: Id::success('Step1'),
                type: OnOneTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'Step3',
                handler: fn() => new OnOneTestDTO(orderId: 'step3'),
                onOne: Id::success('Step2'),
                type: OnOneTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'Step1',
                handler: fn() => new OnOneTestDTO(orderId: 'step1'),
                type: OnOneTestDTO::class,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('Step1'));
        $this->assertTrue($bus->resultIsExists('Step2'));
        $this->assertTrue($bus->resultIsExists('Step3'));
    }

    #[Test]
    public function branching_success_and_fail(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::success('ValidateOrder'));
        $builder->addEvent(BuildEvent::fail('ValidateOrder'));

        $builder->addAction(
            new Action(
                id: 'HandleSuccess',
                handler: fn() => Result::fail(),
                onOne: Id::success('ValidateOrder'),
                externalAccess: true,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'HandleFailure',
                handler: fn() => Result::fail(),
                onOne: Id::fail('ValidateOrder'),
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'ValidateOrder',
                handler: fn() => Result::fail(),
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertFalse($bus->resultIsExists('HandleSuccess'));
        $this->assertTrue($bus->resultIsExists('HandleFailure'));
    }
}
