<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Actor\Context\ActorContext;
use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\Build\Event as BuildEvent;
use Duyler\EventBus\Build\Id;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Event;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Formatter\IdFormatter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final readonly class OnAnyTestDTO
{
    public function __construct(
        public string $source = '',
    ) {}
}

class OnAnySubscriptionTest extends TestCase
{
    #[Test]
    public function execute_when_any_of_events_occur(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new BuildEvent(id: 'Event1'));

        $builder->addActor(
            new Actor(
                id: 'LogActivity',
                handler: fn() => Result::fail(),
                onAny: [
                    'Event1' . IdFormatter::DELIMITER . 'Success',
                ],
                externalAccess: true,
            ),
        );

        $bus = $builder->build();

        $bus->dispatchEvent(new Event(
            id: 'Event1',
        ));

        $bus->run();

        $this->assertTrue($bus->resultIsExists('LogActivity'));
    }

    #[Test]
    public function get_data_from_first_triggered_event(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new BuildEvent(id: 'Event1', type: OnAnyTestDTO::class));

        $builder->addActor(
            new Actor(
                id: 'ProcessData',
                handler: fn(ActorContext $context) => $context->argument(),
                onAny: [
                    'Event1' . IdFormatter::DELIMITER . 'Success',
                ],
                argument: OnAnyTestDTO::class,
                type: OnAnyTestDTO::class,
                externalAccess: true,
            ),
        );

        $bus = $builder->build();

        $bus->dispatchEvent(new Event(
            id: 'Event1',
            data: new OnAnyTestDTO(source: 'Event1'),
        ));

        $bus->run();

        $this->assertTrue($bus->resultIsExists('ProcessData'));
        $result = $bus->getResult('ProcessData');
        $this->assertInstanceOf(OnAnyTestDTO::class, $result->data);
        $this->assertSame('Event1', $result->data->source);
    }

    #[Test]
    public function empty_on_any_does_not_block_execution(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addActor(
            new Actor(
                id: 'IndependentActor',
                handler: fn() => Result::fail(),
                onAny: [],
                externalAccess: true,
            ),
        );

        $builder->doActor(
            new Actor(
                id: 'TriggerActor',
                handler: fn() => Result::fail(),
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertFalse($bus->resultIsExists('IndependentActor'));
    }

    #[Test]
    public function not_execute_when_no_events_triggered(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new BuildEvent(id: 'Event1'));
        $builder->addEvent(new BuildEvent(id: 'Event2'));

        $builder->addActor(
            new Actor(
                id: 'WaitingActor',
                handler: fn() => Result::fail(),
                onAny: [
                    'Event1' . IdFormatter::DELIMITER . 'Success',
                    'Event2' . IdFormatter::DELIMITER . 'Success',
                ],
                externalAccess: true,
            ),
        );

        $builder->doActor(
            new Actor(
                id: 'MainActor',
                handler: fn() => Result::fail(),
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertFalse($bus->resultIsExists('WaitingActor'));
    }

    #[Test]
    public function actor_subscribes_to_actor_events(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(BuildEvent::success('CreateOrder', OnAnyTestDTO::class));

        $builder->addActor(
            new Actor(
                id: 'Logger',
                handler: fn() => new OnAnyTestDTO(source: 'logger'),
                onAny: [
                    Id::success('CreateOrder'),
                ],
                type: OnAnyTestDTO::class,
                externalAccess: true,
            ),
        );

        $builder->doActor(
            new Actor(
                id: 'CreateOrder',
                handler: fn() => new OnAnyTestDTO(source: 'create'),
                type: OnAnyTestDTO::class,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('CreateOrder'));
        $this->assertTrue($bus->resultIsExists('Logger'));
    }

    #[Test]
    public function on_any_with_multiple_events_registered(): void
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new BuildEvent(id: 'Event1', type: OnAnyTestDTO::class));

        $builder->addActor(
            new Actor(
                id: 'MultiSubscriber',
                handler: fn(ActorContext $context) => $context->argument(),
                onAny: [
                    'Event1' . IdFormatter::DELIMITER . 'Success',
                ],
                argument: OnAnyTestDTO::class,
                type: OnAnyTestDTO::class,
                externalAccess: true,
            ),
        );

        $bus = $builder->build();

        $bus->dispatchEvent(new Event(
            id: 'Event1',
            data: new OnAnyTestDTO(source: 'Event1'),
        ));

        $bus->run();

        $this->assertTrue($bus->resultIsExists('MultiSubscriber'));
    }
}
