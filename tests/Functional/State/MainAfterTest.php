<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\Context;
use Duyler\EventBus\Build\Trigger;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\RollbackActionInterface;
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
    public function remove_action_from_state_handler(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainAfterStateHandlerWithAddDynamicAction());
        $busBuilder->addStateHandler(new MainAfterStateHandlerWithRemoveAction());
        $busBuilder->addStateContext(new Context(
            [
                MainAfterStateHandlerWithRemoveAction::class,
                MainAfterStateHandlerWithAddDynamicAction::class,
            ],
        ));
        $busBuilder->doAction(
            new Action(
                id: 'ActionFromBuilder',
                handler: function (): void {},
                required: [
                    'NotRemovedActionFromBuilder',
                ],
                externalAccess: true,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'NotRemovedActionFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'TriggeredActionFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $busBuilder->addTrigger(
            new Trigger(
                subjectId: 'ActionFromBuilder',
                actionId: 'NotRemovedActionFromBuilder',
            ),
        );

        $busBuilder->addTrigger(
            new Trigger(
                subjectId: 'NotRemovedActionFromBuilder',
                actionId: 'TriggeredActionFromBuilder',
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('ActionFromBuilder'));
        $this->assertTrue($bus->resultIsExists('NotRemovedActionFromBuilder'));
        $this->assertTrue($bus->resultIsExists('TriggeredActionFromBuilder'));
        $this->assertFalse($bus->resultIsExists('RemovableAction'));
        $this->assertFalse($bus->resultIsExists('RemovableHeldAction'));
    }

    #[Test]
    public function remove_trigger_from_state_handler(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainAfterStateHandlerWithTrigger());
        $busBuilder->addStateContext(new Context(
            [MainAfterStateHandlerWithTrigger::class],
        ));
        $busBuilder->doAction(
            new Action(
                id: 'ActionFromTest',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'SubscribedActionFromTest',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();
        $this->assertTrue($bus->resultIsExists('ActionFromTest'));
        $this->assertFalse($bus->resultIsExists('SubscribedActionFromTest'));
    }

    #[Test]
    public function rollback_callback_without_exception(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainAfterStateHandlerWithRollback());
        $busBuilder->addStateContext(new Context(
            [MainAfterStateHandlerWithRollback::class],
        ));
        $busBuilder->doAction(
            new Action(
                id: 'ActionFromBuilder',
                handler: function (): void {},
                rollback: function (): void {},
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();
        $this->assertTrue($bus->resultIsExists('ActionFromBuilder'));
    }

    #[Test]
    public function rollback_class_without_exception(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainAfterStateHandlerWithRollback());
        $busBuilder->addStateContext(new Context(
            [MainAfterStateHandlerWithRollback::class],
        ));
        $busBuilder->doAction(
            new Action(
                id: 'ActionFromBuilder',
                handler: function (): void {},
                rollback: Rollback::class,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();
        $this->assertTrue($bus->resultIsExists('ActionFromBuilder'));
    }

    #[Test]
    public function rollback_callback_with_argument(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainAfterStateHandlerWithRollback());
        $busBuilder->addStateContext(new Context(
            [MainAfterStateHandlerWithRollback::class],
        ));
        $busBuilder->doAction(
            new Action(
                id: 'ActionFromBuilder',
                handler: function (\Duyler\EventBus\Action\Context\ActionContext $context): void {},
                required: [
                    'ActionWithContract',
                ],
                argument: stdClass::class,
                rollback: function (RollbackDto $rollbackService): void {},
                externalAccess: true,
            ),
        );

        $busBuilder->doAction(
            new Action(
                id: 'ActionWithContract',
                handler: fn(): stdClass =>  new stdClass(),
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();
        $this->assertTrue($bus->resultIsExists('ActionFromBuilder'));
        $this->assertTrue($bus->resultIsExists('ActionWithContract'));
    }

    #[Test]
    public function get_action_by_contract(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainAfterStateHandlerWithAction());
        $busBuilder->addStateContext(new Context(
            [MainAfterStateHandlerWithAction::class],
        ));
        $busBuilder->doAction(
            new Action(
                id: 'ActionFromBuilder',
                handler: fn(): stdClass => new stdClass(),
                type: stdClass::class,
                immutable: false,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();
        $this->assertTrue($bus->resultIsExists('ActionFromBuilder'));
    }
}

class MainAfterStateHandlerWithRemoveAction implements MainAfterStateHandlerInterface
{
    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        $action = $stateService->getById('NotRemovedActionFromBuilder');
        if ($stateService->resultIsExists($action->id)) {
            $stateService->removeAction('NotRemovedActionFromBuilder');
        }

        $stateService->removeAction('RemovableAction');
        $stateService->removeAction('RemovableHeldAction');

        $stateService->addSharedService(
            new \Duyler\EventBus\Build\SharedService(class: $action::class, service: $action),
        );
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return ['ActionFromBuilder'];
    }
}

class MainAfterStateHandlerWithAddDynamicAction implements MainAfterStateHandlerInterface
{
    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        $stateService->addAction(
            new Action(
                id: 'RemovableAction',
                handler: function (): void {},
            ),
        );

        $stateService->doAction(
            new Action(
                id: 'RemovableHeldAction',
                handler: function (): void {},
                required: ['RemovableAction'],
            ),
        );

        $stateService->addTrigger(
            new Trigger(
                subjectId: 'ActionFromBuilder',
                actionId: 'RemovableAction',
            ),
        );
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return ['ActionFromBuilder'];
    }
}

class MainAfterStateHandlerWithRollback implements MainAfterStateHandlerInterface
{
    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        if ($stateService->getActionId() === 'ActionFromBuilder') {
            $stateService->getResultData();
        }

        if ($stateService->resultIsExists('ActionFromBuilder')) {
            $stateService->rollbackWithoutException();
        }

        if ($stateService->resultIsExists('ActionWithContract')) {
            $stateService->rollbackWithoutException();
        }
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [];
    }
}

class MainAfterStateHandlerWithTrigger implements MainAfterStateHandlerInterface
{
    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        $stateService->addTrigger(
            new Trigger(
                subjectId: 'ActionFromTest',
                actionId: 'SubscribedActionFromTest',
            ),
        );

        $stateService->triggerIsExists(
            new Trigger(
                subjectId: 'ActionFromTest',
                actionId: 'SubscribedActionFromTest',
            ),
        );

        $stateService->removeTrigger(
            new Trigger(
                subjectId: 'ActionFromTest',
                actionId: 'SubscribedActionFromTest',
            ),
        );
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [];
    }
}

class MainAfterStateHandlerWithAction implements MainAfterStateHandlerInterface
{
    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        $stateService->getByContract(stdClass::class);
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [];
    }
}

class Rollback implements RollbackActionInterface
{
    #[Override]
    public function run(RollbackDto $rollback): void
    {
        ResultStatus::Success === $rollback->result->status;
    }
}
