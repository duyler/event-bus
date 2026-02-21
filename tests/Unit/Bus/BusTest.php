<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Bus;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\Event;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Event as EventDto;
use Duyler\EventBus\Formatter\IdFormatter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

class BusTest extends TestCase
{
    #[Test]
    public function task_with_subscription_type_none_passes_check(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->doAction(
            new Action(
                id: 'TestAction',
                handler: fn(): stdClass => new stdClass(),
                type: stdClass::class,
                immutable: false,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('TestAction'));
    }

    #[Test]
    public function task_with_on_one_and_triggered_event_passes_check(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new Event(id: 'TriggeredEvent'));

        $builder->addAction(
            new Action(
                id: 'SubscribedAction',
                handler: fn(): stdClass => new stdClass(),
                type: stdClass::class,
                immutable: false,
                onOne: 'TriggeredEvent' . IdFormatter::DELIMITER . 'Success',
            ),
        );

        $bus = $builder->build();
        $bus->dispatchEvent(new EventDto(id: 'TriggeredEvent' . IdFormatter::DELIMITER . 'Success'));
        $bus->run();

        $this->assertTrue($bus->resultIsExists('SubscribedAction'));
    }

    #[Test]
    public function task_with_on_one_and_not_triggered_event_is_blocked(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new Event(id: 'NotTriggeredEvent'));

        $builder->doAction(
            new Action(
                id: 'SubscribedAction',
                handler: fn(): stdClass => new stdClass(),
                type: stdClass::class,
                immutable: false,
                onOne: 'NotTriggeredEvent' . IdFormatter::DELIMITER . 'Success',
            ),
        );

        $bus = $builder->build();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('TaskQueue is empty');

        $bus->run();
    }

    #[Test]
    public function task_with_on_any_and_triggered_events_passes(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new Event(id: 'Event1'));
        $builder->addEvent(new Event(id: 'Event2'));

        $builder->addAction(
            new Action(
                id: 'SubscribedAction',
                handler: fn(): stdClass => new stdClass(),
                type: stdClass::class,
                immutable: false,
                onAny: [
                    'Event1' . IdFormatter::DELIMITER . 'Success',
                    'Event2' . IdFormatter::DELIMITER . 'Success',
                ],
            ),
        );

        $bus = $builder->build();
        $bus->dispatchEvent(new EventDto(id: 'Event1' . IdFormatter::DELIMITER . 'Success'));
        $bus->dispatchEvent(new EventDto(id: 'Event2' . IdFormatter::DELIMITER . 'Success'));
        $bus->run();

        $this->assertTrue($bus->resultIsExists('SubscribedAction'));
    }

    #[Test]
    public function task_with_on_all_and_partial_events_is_blocked(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new Event(id: 'Event1'));
        $builder->addEvent(new Event(id: 'Event2'));
        $builder->addEvent(new Event(id: 'Event3'));

        $builder->doAction(
            new Action(
                id: 'SubscribedAction',
                handler: fn(): stdClass => new stdClass(),
                type: stdClass::class,
                immutable: false,
                onAll: [
                    'Event1' . IdFormatter::DELIMITER . 'Success',
                    'Event2' . IdFormatter::DELIMITER . 'Success',
                    'Event3' . IdFormatter::DELIMITER . 'Success',
                ],
            ),
        );

        $bus = $builder->build();
        $bus->dispatchEvent(new EventDto(id: 'Event1' . IdFormatter::DELIMITER . 'Success'));
        $bus->dispatchEvent(new EventDto(id: 'Event3' . IdFormatter::DELIMITER . 'Success'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('TaskQueue is empty');

        $bus->run();
    }

    #[Test]
    public function task_with_on_all_and_all_events_passes(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new Event(id: 'Event1'));
        $builder->addEvent(new Event(id: 'Event2'));

        $builder->addAction(
            new Action(
                id: 'SubscribedAction',
                handler: fn(): stdClass => new stdClass(),
                type: stdClass::class,
                immutable: false,
                onAll: [
                    'Event1' . IdFormatter::DELIMITER . 'Success',
                    'Event2' . IdFormatter::DELIMITER . 'Success',
                ],
            ),
        );

        $bus = $builder->build();
        $bus->dispatchEvent(new EventDto(id: 'Event1' . IdFormatter::DELIMITER . 'Success'));
        $bus->dispatchEvent(new EventDto(id: 'Event2' . IdFormatter::DELIMITER . 'Success'));
        $bus->run();

        $this->assertTrue($bus->resultIsExists('SubscribedAction'));
    }

    #[Test]
    public function held_task_activates_when_event_triggered(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new Event(id: 'LateEvent'));

        $builder->doAction(
            new Action(
                id: 'SubscribedAction',
                handler: fn(): stdClass => new stdClass(),
                type: stdClass::class,
                immutable: false,
                onOne: 'LateEvent' . IdFormatter::DELIMITER . 'Success',
            ),
        );

        $bus = $builder->build();

        $bus->dispatchEvent(new EventDto(id: 'LateEvent' . IdFormatter::DELIMITER . 'Success'));

        $bus->run();

        $this->assertTrue($bus->resultIsExists('SubscribedAction'));
    }

    #[Test]
    public function action_with_on_one_and_action_result_event(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new Event(id: 'TriggerAction'));

        $builder->doAction(
            new Action(
                id: 'TriggerAction',
                handler: fn(): stdClass => new stdClass(),
                type: stdClass::class,
                immutable: false,
            ),
        );

        $builder->addAction(
            new Action(
                id: 'SubscribedAction',
                handler: fn(): stdClass => new stdClass(),
                type: stdClass::class,
                immutable: false,
                onOne: 'TriggerAction' . IdFormatter::DELIMITER . 'Success',
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('TriggerAction'));
        $this->assertTrue($bus->resultIsExists('SubscribedAction'));
    }

    #[Test]
    public function action_with_on_one_waits_for_action_fail_event(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new Event(id: 'FailingAction', status: \Duyler\EventBus\Enum\ResultStatus::Fail));

        $builder->doAction(
            new Action(
                id: 'FailingAction',
                handler: fn() => \Duyler\EventBus\Dto\Result::fail(),
            ),
        );

        $builder->addAction(
            new Action(
                id: 'SubscribedAction',
                handler: fn(): stdClass => new stdClass(),
                type: stdClass::class,
                immutable: false,
                onOne: 'FailingAction' . IdFormatter::DELIMITER . 'Fail',
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('FailingAction'));
        $this->assertTrue($bus->resultIsExists('SubscribedAction'));
    }

    #[Test]
    public function subscription_checker_validates_after_lock_check(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new Event(id: 'RequiredEvent'));

        $builder->addAction(
            new Action(
                id: 'LockedAction',
                handler: fn(): stdClass => new stdClass(),
                type: stdClass::class,
                immutable: false,
                lock: true,
                onOne: 'RequiredEvent' . IdFormatter::DELIMITER . 'Success',
            ),
        );

        $bus = $builder->build();
        $bus->dispatchEvent(new EventDto(id: 'RequiredEvent' . IdFormatter::DELIMITER . 'Success'));
        $bus->run();

        $this->assertTrue($bus->resultIsExists('LockedAction'));
    }

    #[Test]
    public function subscription_not_satisfied_blocks_execution(): void
    {
        $builder = new BusBuilder(new BusConfig(allowSkipUnresolvedActions: false));

        $builder->addEvent(new Event(id: 'NeverTriggeredEvent'));

        $builder->doAction(
            new Action(
                id: 'WaitingAction',
                handler: fn(): stdClass => new stdClass(),
                type: stdClass::class,
                immutable: false,
                onOne: 'NeverTriggeredEvent' . IdFormatter::DELIMITER . 'Success',
            ),
        );

        $bus = $builder->build();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('TaskQueue is empty');

        $bus->run();
    }
}
