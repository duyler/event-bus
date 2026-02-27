<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Action\Context\ActionContext;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\Event as BuildEvent;
use Duyler\EventBus\Build\Id;
use Duyler\EventBus\Build\Type;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Event;
use Duyler\EventBus\Dto\Result;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final readonly class ReactiveTestDTO
{
    public function __construct(
        public string $orderId = '',
        public int $amount = 0,
    ) {}
}

class ReactiveSubscriptionTest extends TestCase
{
    #[Test]
    public function on_all_with_required_and_depends_on(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::success('PaymentApproved', ReactiveTestDTO::class));
        $builder->addEvent(BuildEvent::success('InventoryReserved', ReactiveTestDTO::class));

        $builder->doAction(
            new Action(
                id: 'CreateOrder',
                handler: fn() => new ReactiveTestDTO(orderId: 'order-123', amount: 100),
                type: ReactiveTestDTO::class,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'CompleteOrder',
                handler: fn(ActionContext $context) => $context->argument(),
                onAll: [
                    Id::success('PaymentApproved'),
                    Id::success('InventoryReserved'),
                ],
                required: ['CreateOrder'],
                dependsOn: [Type::of(ReactiveTestDTO::class)],
                argument: ReactiveTestDTO::class,
                type: ReactiveTestDTO::class,
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->dispatchEvent(new Event(
            id: 'PaymentApproved',
            data: new ReactiveTestDTO(orderId: 'order-123', amount: 100),
        ));
        $bus->dispatchEvent(new Event(
            id: 'InventoryReserved',
            data: new ReactiveTestDTO(orderId: 'order-123'),
        ));
        $bus->run();

        $this->assertTrue($bus->resultIsExists('CreateOrder'));
        $this->assertTrue($bus->resultIsExists('CompleteOrder'));
    }

    #[Test]
    public function chain_of_actions_via_on_one(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::success('CreateOrder', ReactiveTestDTO::class));
        $builder->addEvent(BuildEvent::success('ValidateOrder', ReactiveTestDTO::class));
        $builder->addEvent(BuildEvent::success('ProcessPayment', ReactiveTestDTO::class));

        $builder->addAction(
            new Action(
                id: 'ValidateOrder',
                handler: fn() => new ReactiveTestDTO(orderId: 'validated'),
                onOne: Id::success('CreateOrder'),
                type: ReactiveTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'ProcessPayment',
                handler: fn() => new ReactiveTestDTO(orderId: 'paid'),
                onOne: Id::success('ValidateOrder'),
                type: ReactiveTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'ShipOrder',
                handler: fn() => new ReactiveTestDTO(orderId: 'shipped'),
                onOne: Id::success('ProcessPayment'),
                type: ReactiveTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'CreateOrder',
                handler: fn() => new ReactiveTestDTO(orderId: 'created'),
                type: ReactiveTestDTO::class,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('CreateOrder'));
        $this->assertTrue($bus->resultIsExists('ValidateOrder'));
        $this->assertTrue($bus->resultIsExists('ProcessPayment'));
        $this->assertTrue($bus->resultIsExists('ShipOrder'));
    }

    #[Test]
    public function branching_different_actions_on_success_fail(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::success('HandleSuccess', ReactiveTestDTO::class));
        $builder->addEvent(BuildEvent::success('HandleFailure', ReactiveTestDTO::class));
        $builder->addEvent(BuildEvent::success('RiskCheck'));
        $builder->addEvent(BuildEvent::fail('RiskCheck'));

        $builder->addAction(
            new Action(
                id: 'HandleSuccess',
                handler: fn() => new ReactiveTestDTO(orderId: 'success'),
                onOne: Id::success('RiskCheck'),
                type: ReactiveTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'HandleFailure',
                handler: fn() => new ReactiveTestDTO(orderId: 'failure'),
                onOne: Id::fail('RiskCheck'),
                type: ReactiveTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'FinalSuccess',
                handler: fn() => new ReactiveTestDTO(orderId: 'final-success'),
                onOne: Id::success('HandleSuccess'),
                type: ReactiveTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'FinalFailure',
                handler: fn() => new ReactiveTestDTO(orderId: 'final-failure'),
                onOne: Id::success('HandleFailure'),
                type: ReactiveTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'RiskCheck',
                handler: fn() => Result::fail(),
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertFalse($bus->resultIsExists('HandleSuccess'));
        $this->assertTrue($bus->resultIsExists('HandleFailure'));
        $this->assertFalse($bus->resultIsExists('FinalSuccess'));
        $this->assertTrue($bus->resultIsExists('FinalFailure'));
    }

    #[Test]
    public function on_one_with_required_actions(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::success('LoadConfig', ReactiveTestDTO::class));

        $builder->doAction(
            new Action(
                id: 'LoadConfig',
                handler: fn() => new ReactiveTestDTO(orderId: 'config'),
                type: ReactiveTestDTO::class,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'ConnectDatabase',
                handler: fn() => new ReactiveTestDTO(orderId: 'db'),
                type: ReactiveTestDTO::class,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'StartApplication',
                handler: fn() => new ReactiveTestDTO(orderId: 'started'),
                onOne: Id::success('LoadConfig'),
                required: ['ConnectDatabase'],
                type: ReactiveTestDTO::class,
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('LoadConfig'));
        $this->assertTrue($bus->resultIsExists('ConnectDatabase'));
        $this->assertTrue($bus->resultIsExists('StartApplication'));
    }

    #[Test]
    public function on_any_with_data_transfer(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::success('Event1', ReactiveTestDTO::class));

        $builder->addAction(
            new Action(
                id: 'DataProcessor',
                handler: fn(ActionContext $context) => $context->argument(),
                onAny: [
                    Id::success('Event1'),
                ],
                argument: ReactiveTestDTO::class,
                type: ReactiveTestDTO::class,
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->dispatchEvent(new Event(
            id: 'Event1',
            data: new ReactiveTestDTO(orderId: 'order-1', amount: 100),
        ));
        $bus->run();

        $result = $bus->getResult('DataProcessor');
        $this->assertInstanceOf(ReactiveTestDTO::class, $result->data);
        $this->assertSame('order-1', $result->data->orderId);
    }

    #[Test]
    public function complex_workflow_with_multiple_subscription_types(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::success('ExternalTrigger', ReactiveTestDTO::class));
        $builder->addEvent(BuildEvent::success('OnOneAction', ReactiveTestDTO::class));

        $builder->addAction(
            new Action(
                id: 'OnOneAction',
                handler: fn() => new ReactiveTestDTO(orderId: 'on-one'),
                onOne: Id::success('ExternalTrigger'),
                type: ReactiveTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'OnAnyAction',
                handler: fn() => new ReactiveTestDTO(orderId: 'on-any'),
                onAny: [
                    Id::success('ExternalTrigger'),
                    Id::success('OnOneAction'),
                ],
                type: ReactiveTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'OnAllAction',
                handler: fn() => new ReactiveTestDTO(orderId: 'on-all'),
                onAll: [
                    Id::success('ExternalTrigger'),
                    Id::success('OnOneAction'),
                ],
                type: ReactiveTestDTO::class,
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->dispatchEvent(new Event(
            id: 'ExternalTrigger',
            data: new ReactiveTestDTO(orderId: 'trigger'),
        ));
        $bus->run();

        $this->assertTrue($bus->resultIsExists('OnOneAction'));
        $this->assertTrue($bus->resultIsExists('OnAnyAction'));
        $this->assertTrue($bus->resultIsExists('OnAllAction'));
    }

    #[Test]
    public function diamond_dependency_pattern(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::success('Start', ReactiveTestDTO::class));
        $builder->addEvent(BuildEvent::success('BranchA', ReactiveTestDTO::class));
        $builder->addEvent(BuildEvent::success('BranchB', ReactiveTestDTO::class));

        $builder->doAction(
            new Action(
                id: 'Start',
                handler: fn() => new ReactiveTestDTO(orderId: 'start'),
                type: ReactiveTestDTO::class,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'BranchA',
                handler: fn() => new ReactiveTestDTO(orderId: 'a'),
                onOne: Id::success('Start'),
                type: ReactiveTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'BranchB',
                handler: fn() => new ReactiveTestDTO(orderId: 'b'),
                onOne: Id::success('Start'),
                type: ReactiveTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'Merge',
                handler: fn() => new ReactiveTestDTO(orderId: 'merge'),
                onAll: [
                    Id::success('BranchA'),
                    Id::success('BranchB'),
                ],
                type: ReactiveTestDTO::class,
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('Start'));
        $this->assertTrue($bus->resultIsExists('BranchA'));
        $this->assertTrue($bus->resultIsExists('BranchB'));
        $this->assertTrue($bus->resultIsExists('Merge'));
    }

    #[Test]
    public function error_recovery_with_on_any(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::success('FallbackHandler', ReactiveTestDTO::class));
        $builder->addEvent(BuildEvent::success('NotificationService', ReactiveTestDTO::class));
        $builder->addEvent(BuildEvent::fail('MainAction'));

        $builder->addAction(
            new Action(
                id: 'FallbackHandler',
                handler: fn() => new ReactiveTestDTO(orderId: 'fallback'),
                onOne: Id::fail('MainAction'),
                type: ReactiveTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'NotificationService',
                handler: fn() => new ReactiveTestDTO(orderId: 'notified'),
                onAny: [
                    Id::success('FallbackHandler'),
                ],
                type: ReactiveTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'MainAction',
                handler: fn() => Result::fail(),
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('FallbackHandler'));
        $this->assertTrue($bus->resultIsExists('NotificationService'));
    }
}
