<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Actor\Context\ActorContext;
use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\Build\Context;
use Duyler\EventBus\Build\Event as BuildEvent;
use Duyler\EventBus\Build\Id;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\State\MainCyclicStateHandlerInterface;
use Duyler\EventBus\Dto\Event;
use Duyler\EventBus\State\Service\StateMainCyclicService;
use Duyler\EventBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final readonly class LoopTestDTO
{
    public function __construct(
        public int $iteration = 0,
    ) {}
}

class LongRunningModeTest extends TestCase
{
    #[Test]
    public function repeat_actor_with_on_one_in_cyclic_mode(): void
    {
        $stateHandler = new LoopModeOnOneStateHandler();

        $builder = new BusBuilder(new BusConfig());
        $builder->addStateHandler($stateHandler);
        $builder->addStateContext(new Context([LoopModeOnOneStateHandler::class]));

        $builder->addEvent(BuildEvent::success('TriggerEvent', LoopTestDTO::class));

        $builder->doActor(
            new Actor(
                id: 'InitialActor',
                handler: fn() => new LoopTestDTO(),
                type: LoopTestDTO::class,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertSame(3, $stateHandler->counter);
    }

    #[Test]
    public function event_accumulation_in_cyclic_mode(): void
    {
        $stateHandler = new LoopModeAccumulationStateHandler();

        $builder = new BusBuilder(new BusConfig());
        $builder->addStateHandler($stateHandler);
        $builder->addStateContext(new Context([LoopModeAccumulationStateHandler::class]));

        $builder->addEvent(BuildEvent::success('AccumulateEvent', LoopTestDTO::class));

        $builder->addActor(
            new Actor(
                id: 'AccumulatorActor',
                handler: fn(ActorContext $context) => $context->argument(),
                onOne: Id::success('AccumulateEvent'),
                argument: LoopTestDTO::class,
                type: LoopTestDTO::class,
                externalAccess: true,
                repeatable: true,
            ),
        );

        $builder->doActor(
            new Actor(
                id: 'InitialActor',
                handler: fn() => new LoopTestDTO(),
                type: LoopTestDTO::class,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertSame(3, $stateHandler->dispatchCount);
    }

    #[Test]
    public function correct_operation_after_multiple_cycles(): void
    {
        $stateHandler = new LoopModeMultipleCyclesStateHandler();

        $builder = new BusBuilder(new BusConfig());
        $builder->addStateHandler($stateHandler);
        $builder->addStateContext(new Context([LoopModeMultipleCyclesStateHandler::class]));

        $builder->addEvent(BuildEvent::success('CycleEvent'));

        $builder->addActor(
            new Actor(
                id: 'CycleActor',
                handler: fn() => new LoopTestDTO(),
                onOne: Id::success('CycleEvent'),
                type: LoopTestDTO::class,
                externalAccess: true,
                repeatable: true,
            ),
        );

        $builder->doActor(
            new Actor(
                id: 'StartActor',
                handler: fn() => new LoopTestDTO(),
                type: LoopTestDTO::class,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertSame(5, $stateHandler->cycleCount);
    }

    #[Test]
    public function on_any_in_cyclic_mode(): void
    {
        $stateHandler = new LoopModeOnAnyStateHandler();

        $builder = new BusBuilder(new BusConfig());
        $builder->addStateHandler($stateHandler);
        $builder->addStateContext(new Context([LoopModeOnAnyStateHandler::class]));

        $builder->addEvent(BuildEvent::success('Event1'));

        $builder->addActor(
            new Actor(
                id: 'OnAnyLoopActor',
                handler: fn() => new LoopTestDTO(),
                onAny: [
                    Id::success('Event1'),
                ],
                type: LoopTestDTO::class,
                externalAccess: true,
                repeatable: true,
            ),
        );

        $builder->doActor(
            new Actor(
                id: 'InitialActor',
                handler: fn() => new LoopTestDTO(),
                type: LoopTestDTO::class,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertSame(1, $stateHandler->executionCount);
    }

    #[Test]
    public function on_all_in_cyclic_mode(): void
    {
        $stateHandler = new LoopModeOnAllStateHandler();

        $builder = new BusBuilder(new BusConfig());
        $builder->addStateHandler($stateHandler);
        $builder->addStateContext(new Context([LoopModeOnAllStateHandler::class]));

        $builder->addEvent(BuildEvent::success('RequiredEvent1'));
        $builder->addEvent(BuildEvent::success('RequiredEvent2'));

        $builder->addActor(
            new Actor(
                id: 'OnAllLoopActor',
                handler: fn() => new LoopTestDTO(),
                onAll: [
                    Id::success('RequiredEvent1'),
                    Id::success('RequiredEvent2'),
                ],
                type: LoopTestDTO::class,
                externalAccess: true,
                repeatable: true,
            ),
        );

        $builder->doActor(
            new Actor(
                id: 'InitialActor',
                handler: fn() => new LoopTestDTO(),
                type: LoopTestDTO::class,
            ),
        );

        $bus = $builder->build();
        $bus->run();

        $this->assertSame(2, $stateHandler->executionCount);
    }
}

class LoopModeOnOneStateHandler implements MainCyclicStateHandlerInterface
{
    public int $counter = 0;

    #[Override]
    public function handle(StateMainCyclicService $stateService, StateContext $context): void
    {
        if ($this->counter < 3) {
            if (false === $stateService->actorIsExists('RepeatedActor')) {
                $stateService->addActor(
                    new Actor(
                        id: 'RepeatedActor',
                        handler: fn() => new LoopTestDTO(),
                        onOne: Id::success('TriggerEvent'),
                        type: LoopTestDTO::class,
                        externalAccess: true,
                        repeatable: true,
                    ),
                );
            }

            $stateService->dispatchEvent(new Event(
                id: 'TriggerEvent',
                data: new LoopTestDTO(iteration: $this->counter),
            ));

            $this->counter++;
        }
    }
}

class LoopModeAccumulationStateHandler implements MainCyclicStateHandlerInterface
{
    public int $dispatchCount = 0;
    private int $iteration = 0;

    #[Override]
    public function handle(StateMainCyclicService $stateService, StateContext $context): void
    {
        if ($this->iteration < 3) {
            $this->iteration++;

            $stateService->dispatchEvent(new Event(
                id: 'AccumulateEvent',
                data: new LoopTestDTO(iteration: $this->iteration),
            ));

            $this->dispatchCount++;
        }
    }
}

class LoopModeMultipleCyclesStateHandler implements MainCyclicStateHandlerInterface
{
    public int $cycleCount = 0;

    #[Override]
    public function handle(StateMainCyclicService $stateService, StateContext $context): void
    {
        if ($this->cycleCount < 5) {
            $stateService->dispatchEvent(new Event(
                id: 'CycleEvent',
            ));

            $this->cycleCount++;
        }
    }
}

class LoopModeOnAnyStateHandler implements MainCyclicStateHandlerInterface
{
    public int $executionCount = 0;

    #[Override]
    public function handle(StateMainCyclicService $stateService, StateContext $context): void
    {
        if (0 === $this->executionCount) {
            $stateService->dispatchEvent(new Event(
                id: 'Event1',
            ));

            $this->executionCount++;
        }
    }
}

class LoopModeOnAllStateHandler implements MainCyclicStateHandlerInterface
{
    public int $executionCount = 0;
    private int $iteration = 0;

    #[Override]
    public function handle(StateMainCyclicService $stateService, StateContext $context): void
    {
        if ($this->iteration < 2) {
            $this->iteration++;

            $stateService->dispatchEvent(new Event(
                id: 'RequiredEvent1',
            ));

            $stateService->dispatchEvent(new Event(
                id: 'RequiredEvent2',
            ));

            $this->executionCount++;
        }
    }
}
