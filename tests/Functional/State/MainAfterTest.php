<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\Build\Context;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\RollbackActorInterface;
use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\Dto\Rollback as RollbackDto;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\StateContext;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class MainAfterTest extends TestCase
{
    #[Test]
    public function remove_actor_from_state_handler(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainAfterStateHandlerWithAddDynamicActor());
        $busBuilder->addStateHandler(new MainAfterStateHandlerWithRemoveActor());
        $busBuilder->addStateContext(new Context(
            [
                MainAfterStateHandlerWithRemoveActor::class,
                MainAfterStateHandlerWithAddDynamicActor::class,
            ],
        ));
        $busBuilder->doActor(
            new Actor(
                id: 'ActorFromBuilder',
                handler: function (): void {},
                required: [
                    'NotRemovedActorFromBuilder',
                ],
                externalAccess: true,
            ),
        );

        $busBuilder->addActor(
            new Actor(
                id: 'NotRemovedActorFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $busBuilder->addActor(
            new Actor(
                id: 'TriggeredActorFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('ActorFromBuilder'));
        $this->assertTrue($bus->resultIsExists('NotRemovedActorFromBuilder'));
    }

    #[Test]
    public function rollback_callback_without_exception(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainAfterStateHandlerWithRollback());
        $busBuilder->addStateContext(new Context(
            [MainAfterStateHandlerWithRollback::class],
        ));
        $busBuilder->doActor(
            new Actor(
                id: 'ActorFromBuilder',
                handler: function (): void {},
                rollback: function (): void {},
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();
        $this->assertTrue($bus->resultIsExists('ActorFromBuilder'));
    }

    #[Test]
    public function rollback_class_without_exception(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainAfterStateHandlerWithRollback());
        $busBuilder->addStateContext(new Context(
            [MainAfterStateHandlerWithRollback::class],
        ));
        $busBuilder->doActor(
            new Actor(
                id: 'ActorFromBuilder',
                handler: function (): void {},
                rollback: Rollback::class,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();
        $this->assertTrue($bus->resultIsExists('ActorFromBuilder'));
    }

    #[Test]
    public function rollback_callback_with_argument(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainAfterStateHandlerWithRollback());
        $busBuilder->addStateContext(new Context(
            [MainAfterStateHandlerWithRollback::class],
        ));
        $busBuilder->doActor(
            new Actor(
                id: 'ActorFromBuilder',
                handler: function (\Duyler\EventBus\Actor\Context\ActorContext $context): void {},
                required: [
                    'ActorWithContract',
                ],
                argument: stdClass::class,
                rollback: function (RollbackDto $rollbackService): void {},
                externalAccess: true,
            ),
        );

        $busBuilder->doActor(
            new Actor(
                id: 'ActorWithContract',
                handler: fn(): stdClass =>  new stdClass(),
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();
        $this->assertTrue($bus->resultIsExists('ActorFromBuilder'));
        $this->assertTrue($bus->resultIsExists('ActorWithContract'));
    }

    #[Test]
    public function get_actor_by_contract(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainAfterStateHandlerWithActor());
        $busBuilder->addStateContext(new Context(
            [MainAfterStateHandlerWithActor::class],
        ));
        $busBuilder->doActor(
            new Actor(
                id: 'ActorFromBuilder',
                handler: fn(): stdClass => new stdClass(),
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();
        $this->assertTrue($bus->resultIsExists('ActorFromBuilder'));
    }
}

class MainAfterStateHandlerWithRemoveActor implements MainAfterStateHandlerInterface
{
    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        $actor = $stateService->getById('NotRemovedActorFromBuilder');
        if ($stateService->resultIsExists($actor->id)) {
            $stateService->removeActor('NotRemovedActorFromBuilder');
        }

        $stateService->removeActor('RemovableActor');
        $stateService->removeActor('RemovableHeldActor');

        $stateService->addSharedService(
            new \Duyler\EventBus\Build\SharedService(class: $actor::class, service: $actor),
        );
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return ['ActorFromBuilder'];
    }
}

class MainAfterStateHandlerWithAddDynamicActor implements MainAfterStateHandlerInterface
{
    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        $stateService->addActor(
            new Actor(
                id: 'RemovableActor',
                handler: function (): void {},
            ),
        );

        $stateService->doActor(
            new Actor(
                id: 'RemovableHeldActor',
                handler: function (): void {},
                required: ['RemovableActor'],
            ),
        );
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return ['ActorFromBuilder'];
    }
}

class MainAfterStateHandlerWithRollback implements MainAfterStateHandlerInterface
{
    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        if ($stateService->getActorId() === 'ActorFromBuilder') {
            $stateService->getResultData();
        }

        if ($stateService->resultIsExists('ActorFromBuilder')) {
            $stateService->rollbackWithoutException();
        }

        if ($stateService->resultIsExists('ActorWithContract')) {
            $stateService->rollbackWithoutException();
        }
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [];
    }
}

class MainAfterStateHandlerWithActor implements MainAfterStateHandlerInterface
{
    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        $stateService->getByType(stdClass::class);
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [];
    }
}

class Rollback implements RollbackActorInterface
{
    #[Override]
    public function run(RollbackDto $rollback): void
    {
        ResultStatus::Success === $rollback->result->status;
    }
}
