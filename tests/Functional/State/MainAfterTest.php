<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\State;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\RollbackActionInterface;
use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Context;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\Subscription;
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
        $busBuilder->addStateHandler(new MainAfterStateHandlerWithRemoveAction());
        $busBuilder->addStateContext(new Context(
            [MainAfterStateHandlerWithRemoveAction::class],
        ));
        $busBuilder->doAction(
            new Action(
                id: 'ActionFromBuilder',
                handler: function (): void {},
                required: [
                    'RemovedActionFromBuilder',
                ],
                externalAccess: true,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'RemovedActionFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'SubscribedActionFromBuilder',
                handler: function (): void {},
                externalAccess: true,
            ),
        );

        $busBuilder->addSubscription(
            new Subscription(
                subjectId: 'ActionFromBuilder',
                actionId: 'RemovedActionFromBuilder',
            ),
        );

        $busBuilder->addSubscription(
            new Subscription(
                subjectId: 'RemovedActionFromBuilder',
                actionId: 'SubscribedActionFromBuilder',
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();

        $this->assertTrue($bus->resultIsExists('ActionFromBuilder'));
        $this->assertTrue($bus->resultIsExists('RemovedActionFromBuilder'));
        $this->assertTrue($bus->resultIsExists('SubscribedActionFromBuilder'));
    }

    #[Test]
    public function remove_subscription_from_state_handler(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->addStateHandler(new MainAfterStateHandlerWithSubscription());
        $busBuilder->addStateContext(new Context(
            [MainAfterStateHandlerWithSubscription::class],
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
                handler: function (stdClass $argument): void {},
                required: [
                    'ActionWithContract',
                ],
                argument: stdClass::class,
                rollback: function (Result $result, stdClass $argument): void {},
                externalAccess: true,
            ),
        );

        $busBuilder->doAction(
            new Action(
                id: 'ActionWithContract',
                handler: fn(): stdClass =>  new stdClass(),
                contract: stdClass::class,
                externalAccess: true,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();
        $this->assertTrue($bus->resultIsExists('ActionFromBuilder'));
        $this->assertTrue($bus->resultIsExists('ActionWithContract'));
    }
}

class MainAfterStateHandlerWithRemoveAction implements MainAfterStateHandlerInterface
{
    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        if ($stateService->resultIsExists('RemovedActionFromBuilder')) {
            $stateService->removeAction('RemovedActionFromBuilder');
        }
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
        if ($stateService->resultIsExists('ActionFromBuilder')) {
            $stateService->rollbackWithoutException(1);
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

class MainAfterStateHandlerWithSubscription implements MainAfterStateHandlerInterface
{
    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        $stateService->addSubscription(
            new Subscription(
                subjectId: 'ActionFromTest',
                actionId: 'SubscribedActionFromTest',
            ),
        );

        $stateService->subscriptionIsExists(
            new Subscription(
                subjectId: 'ActionFromTest',
                actionId: 'SubscribedActionFromTest',
            ),
        );

        $stateService->removeSubscription(
            new Subscription(
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

class Rollback implements RollbackActionInterface
{
    #[Override]
    public function run(Result $result, object|null $argument): void
    {
        ResultStatus::Success === $result->status;
    }
}
