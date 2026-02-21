<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\Event as BuildEvent;
use Duyler\EventBus\Build\Id;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainCyclicStateHandlerInterface;
use Duyler\EventBus\Dto\Event;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\State\Service\StateMainCyclicService;
use Duyler\EventBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final readonly class OnAllTestDTO
{
    public function __construct(
        public string $value = '',
    ) {}
}

final readonly class OnAllTestDTO2
{
    public function __construct(
        public int $count = 0,
    ) {}
}

class OnAllSubscriptionTest extends TestCase
{
    #[Test]
    public function execute_only_when_all_events_occur(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new BuildEvent(id: 'ValidateCart'));
        $builder->addEvent(new BuildEvent(id: 'ProcessPayment'));

        $builder->addAction(
            new Action(
                id: 'CompleteCheckout',
                handler: fn() => new OnAllTestDTO(value: 'complete'),
                onAll: [
                    'ValidateCart' . IdFormatter::DELIMITER . 'Success',
                    'ProcessPayment' . IdFormatter::DELIMITER . 'Success',
                ],
                type: OnAllTestDTO::class,
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->dispatchEvent(new Event(
            id: 'ValidateCart' . IdFormatter::DELIMITER . 'Success',
        ));
        $bus->dispatchEvent(new Event(
            id: 'ProcessPayment' . IdFormatter::DELIMITER . 'Success',
        ));
        $bus->run();

        $this->assertTrue($bus->resultIsExists('CompleteCheckout'));
    }

    #[Test]
    public function data_from_all_events_available(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new BuildEvent(id: 'Event1', type: OnAllTestDTO::class));
        $builder->addEvent(new BuildEvent(id: 'Event2', type: OnAllTestDTO2::class));

        $builder->addAction(
            new Action(
                id: 'CombinedHandler',
                handler: fn() => new OnAllTestDTO(value: 'combined'),
                onAll: [
                    'Event1' . IdFormatter::DELIMITER . 'Success',
                    'Event2' . IdFormatter::DELIMITER . 'Success',
                ],
                type: OnAllTestDTO::class,
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->dispatchEvent(new Event(
            id: 'Event1' . IdFormatter::DELIMITER . 'Success',
            data: new OnAllTestDTO(value: 'test'),
        ));
        $bus->dispatchEvent(new Event(
            id: 'Event2' . IdFormatter::DELIMITER . 'Success',
            data: new OnAllTestDTO2(count: 42),
        ));
        $bus->run();

        $this->assertTrue($bus->resultIsExists('CombinedHandler'));
        $this->assertTrue($bus->resultIsExists('Event1' . IdFormatter::DELIMITER . 'Success'));
        $this->assertTrue($bus->resultIsExists('Event2' . IdFormatter::DELIMITER . 'Success'));
    }

    #[Test]
    public function partial_events_block_execution(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new BuildEvent(id: 'Event1'));
        $builder->addEvent(new BuildEvent(id: 'Event2'));
        $builder->addEvent(new BuildEvent(id: 'Event3'));

        $builder->addAction(
            new Action(
                id: 'WaitingAction',
                handler: fn() => new OnAllTestDTO(),
                onAll: [
                    'Event1' . IdFormatter::DELIMITER . 'Success',
                    'Event2' . IdFormatter::DELIMITER . 'Success',
                    'Event3' . IdFormatter::DELIMITER . 'Success',
                ],
                type: OnAllTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'MainAction',
                handler: fn() => new OnAllTestDTO(),
                type: OnAllTestDTO::class,
            ),
        );

        $bus = $builder->build();
        $bus->dispatchEvent(new Event(
            id: 'Event1' . IdFormatter::DELIMITER . 'Success',
        ));
        $bus->run();

        $this->assertFalse($bus->resultIsExists('WaitingAction'));
    }

    #[Test]
    public function held_task_activates_on_last_event(): void
    {
        $builder = new BusBuilder(new BusConfig());
        $builder->addStateHandler(new OnAllHeldTaskStateHandler());
        $builder->addEvent(new BuildEvent(id: 'Event1'));
        $builder->addEvent(new BuildEvent(id: 'Event2'));

        $builder->addAction(
            new Action(
                id: 'HeldTaskAction',
                handler: fn() => new OnAllTestDTO(),
                onAll: [
                    'Event1' . IdFormatter::DELIMITER . 'Success',
                    'Event2' . IdFormatter::DELIMITER . 'Success',
                ],
                type: OnAllTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'MainAction',
                handler: fn() => new OnAllTestDTO(),
                type: OnAllTestDTO::class,
            ),
        );

        $bus = $builder->build();
        $bus->dispatchEvent(new Event(
            id: 'Event1' . IdFormatter::DELIMITER . 'Success',
        ));
        $bus->run();

        $this->assertTrue($bus->resultIsExists('HeldTaskAction'));
    }

    #[Test]
    public function on_all_with_action_events(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::success('ValidateOrder', OnAllTestDTO::class));
        $builder->addEvent(BuildEvent::success('ProcessPayment', OnAllTestDTO::class));

        $builder->addAction(
            new Action(
                id: 'FinalizeOrder',
                handler: fn() => new OnAllTestDTO(value: 'finalized'),
                onAll: [
                    Id::success('ValidateOrder'),
                    Id::success('ProcessPayment'),
                ],
                type: OnAllTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'ValidateOrder',
                handler: fn() => new OnAllTestDTO(value: 'validated'),
                type: OnAllTestDTO::class,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'ProcessPayment',
                handler: fn() => new OnAllTestDTO(value: 'paid'),
                type: OnAllTestDTO::class,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('ValidateOrder'));
        $this->assertTrue($bus->resultIsExists('ProcessPayment'));
        $this->assertTrue($bus->resultIsExists('FinalizeOrder'));
    }

    #[Test]
    public function on_all_with_mixed_action_and_external_events(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::success('ExternalApproval', OnAllTestDTO::class));
        $builder->addEvent(BuildEvent::success('InternalAction', OnAllTestDTO::class));

        $builder->addAction(
            new Action(
                id: 'CompleteWorkflow',
                handler: fn() => new OnAllTestDTO(value: 'complete'),
                onAll: [
                    Id::success('InternalAction'),
                    Id::success('ExternalApproval'),
                ],
                type: OnAllTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'InternalAction',
                handler: fn() => new OnAllTestDTO(value: 'internal'),
                type: OnAllTestDTO::class,
            ),
        );

        $bus = $builder->build();
        $bus->dispatchEvent(new Event(
            id: 'ExternalApproval' . IdFormatter::DELIMITER . 'Success',
            data: new OnAllTestDTO(value: 'approved'),
        ));
        $bus->run();

        $this->assertTrue($bus->resultIsExists('InternalAction'));
        $this->assertTrue($bus->resultIsExists('CompleteWorkflow'));
    }

    #[Test]
    public function on_all_empty_array_does_not_execute(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addAction(
            new Action(
                id: 'EmptyOnAllAction',
                handler: fn() => new OnAllTestDTO(),
                onAll: [],
                type: OnAllTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->doAction(
            new Action(
                id: 'MainAction',
                handler: fn() => new OnAllTestDTO(),
                type: OnAllTestDTO::class,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertFalse($bus->resultIsExists('EmptyOnAllAction'));
    }

    #[Test]
    public function on_all_with_fail_status(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::fail('ValidationError'));
        $builder->addEvent(BuildEvent::fail('PaymentError'));

        $builder->addAction(
            new Action(
                id: 'CriticalErrorHandler',
                handler: fn() => new OnAllTestDTO(value: 'error-handled'),
                onAll: [
                    Id::fail('ValidationError'),
                    Id::fail('PaymentError'),
                ],
                type: OnAllTestDTO::class,
                externalAccess: true,
            ),
        );

        $bus = $builder->build();
        $bus->dispatchEvent(new Event(
            id: 'ValidationError' . IdFormatter::DELIMITER . 'Fail',
        ));
        $bus->dispatchEvent(new Event(
            id: 'PaymentError' . IdFormatter::DELIMITER . 'Fail',
        ));
        $bus->run();

        $this->assertTrue($bus->resultIsExists('CriticalErrorHandler'));
    }
}

class OnAllHeldTaskStateHandler implements MainCyclicStateHandlerInterface
{
    #[Override]
    public function handle(StateMainCyclicService $stateService, StateContext $context): void
    {
        if (false === $stateService->resultIsExists('HeldTaskAction')) {
            $stateService->dispatchEvent(new Event(
                id: 'Event2' . IdFormatter::DELIMITER . 'Success',
            ));
        }
    }
}
