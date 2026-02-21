<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Bus;

use Duyler\EventBus\Bus\Action;
use Duyler\EventBus\Bus\CompleteAction;
use Duyler\EventBus\Bus\SubscriptionChecker;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\EventBus\Storage\EventRelationStorage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class SubscriptionCheckerTest extends TestCase
{
    private CompleteActionStorage&MockObject $completeActionStorage;
    private EventRelationStorage&MockObject $eventRelationStorage;
    private SubscriptionChecker $subscriptionChecker;

    protected function setUp(): void
    {
        $this->completeActionStorage = $this->createMock(CompleteActionStorage::class);
        $this->eventRelationStorage = $this->createMock(EventRelationStorage::class);
        $this->subscriptionChecker = new SubscriptionChecker(
            $this->completeActionStorage,
            $this->eventRelationStorage,
        );
    }

    #[Test]
    public function is_satisfied_returns_true_for_subscription_type_none(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: fn(): stdClass => new stdClass(),
        );

        $this->assertTrue($this->subscriptionChecker->isSatisfied($action));
    }

    #[Test]
    public function is_satisfied_returns_true_for_on_one_when_event_triggered(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: fn(): stdClass => new stdClass(),
            onOne: 'ExternalEvent',
        );

        $this->eventRelationStorage
            ->expects($this->once())
            ->method('isExists')
            ->with('ExternalEvent')
            ->willReturn(true);

        $this->assertTrue($this->subscriptionChecker->isSatisfied($action));
    }

    #[Test]
    public function is_satisfied_returns_false_for_on_one_when_event_not_triggered(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: fn(): stdClass => new stdClass(),
            onOne: 'ExternalEvent',
        );

        $this->eventRelationStorage
            ->expects($this->once())
            ->method('isExists')
            ->with('ExternalEvent')
            ->willReturn(false);

        $this->completeActionStorage
            ->expects($this->never())
            ->method('isExists');

        $this->assertFalse($this->subscriptionChecker->isSatisfied($action));
    }

    #[Test]
    public function is_satisfied_returns_true_for_on_one_with_action_event_success(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: fn(): stdClass => new stdClass(),
            onOne: 'SourceAction::Success',
        );

        $sourceAction = new Action(
            id: 'SourceAction',
            externalId: 'SourceAction',
            handler: fn(): stdClass => new stdClass(),
        );

        $completeAction = new CompleteAction(
            action: $sourceAction,
            result: Result::success(new stdClass()),
            taskId: 'task-1',
        );

        $this->eventRelationStorage
            ->expects($this->once())
            ->method('isExists')
            ->with('SourceAction::Success')
            ->willReturn(false);

        $this->completeActionStorage
            ->expects($this->once())
            ->method('isExists')
            ->with('SourceAction')
            ->willReturn(true);

        $this->completeActionStorage
            ->expects($this->once())
            ->method('get')
            ->with('SourceAction')
            ->willReturn($completeAction);

        $this->assertTrue($this->subscriptionChecker->isSatisfied($action));
    }

    #[Test]
    public function is_satisfied_returns_false_for_on_one_with_action_event_fail(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: fn(): stdClass => new stdClass(),
            onOne: 'SourceAction::Success',
        );

        $sourceAction = new Action(
            id: 'SourceAction',
            externalId: 'SourceAction',
            handler: fn(): stdClass => new stdClass(),
        );

        $completeAction = new CompleteAction(
            action: $sourceAction,
            result: Result::fail(),
            taskId: 'task-1',
        );

        $this->eventRelationStorage
            ->expects($this->once())
            ->method('isExists')
            ->with('SourceAction::Success')
            ->willReturn(false);

        $this->completeActionStorage
            ->expects($this->once())
            ->method('isExists')
            ->with('SourceAction')
            ->willReturn(true);

        $this->completeActionStorage
            ->expects($this->once())
            ->method('get')
            ->with('SourceAction')
            ->willReturn($completeAction);

        $this->assertFalse($this->subscriptionChecker->isSatisfied($action));
    }

    #[Test]
    public function is_satisfied_returns_true_for_on_any_with_one_triggered_event(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: fn(): stdClass => new stdClass(),
            onAny: ['Event1', 'Event2', 'Event3'],
        );

        $this->eventRelationStorage
            ->expects($this->exactly(2))
            ->method('isExists')
            ->willReturnMap([
                ['Event1', false],
                ['Event2', true],
            ]);

        $this->assertTrue($this->subscriptionChecker->isSatisfied($action));
    }

    #[Test]
    public function is_satisfied_returns_false_for_on_any_with_no_triggered_events(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: fn(): stdClass => new stdClass(),
            onAny: ['Event1', 'Event2'],
        );

        $this->eventRelationStorage
            ->expects($this->exactly(2))
            ->method('isExists')
            ->willReturnMap([
                ['Event1', false],
                ['Event2', false],
            ]);

        $this->completeActionStorage
            ->expects($this->never())
            ->method('isExists');

        $this->assertFalse($this->subscriptionChecker->isSatisfied($action));
    }

    #[Test]
    public function is_satisfied_returns_true_for_on_all_with_all_triggered_events(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: fn(): stdClass => new stdClass(),
            onAll: ['Event1', 'Event2'],
        );

        $this->eventRelationStorage
            ->expects($this->exactly(2))
            ->method('isExists')
            ->willReturnMap([
                ['Event1', true],
                ['Event2', true],
            ]);

        $this->assertTrue($this->subscriptionChecker->isSatisfied($action));
    }

    #[Test]
    public function is_satisfied_returns_false_for_on_all_with_partial_triggered_events(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: fn(): stdClass => new stdClass(),
            onAll: ['Event1', 'Event2', 'Event3'],
        );

        $this->eventRelationStorage
            ->expects($this->exactly(2))
            ->method('isExists')
            ->willReturnMap([
                ['Event1', true],
                ['Event2', false],
            ]);

        $this->assertFalse($this->subscriptionChecker->isSatisfied($action));
    }

    #[Test]
    public function is_satisfied_checks_external_event_via_event_relation_storage(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: fn(): stdClass => new stdClass(),
            onOne: 'ExternalEvent',
        );

        $this->eventRelationStorage
            ->expects($this->once())
            ->method('isExists')
            ->with('ExternalEvent')
            ->willReturn(true);

        $this->assertTrue($this->subscriptionChecker->isSatisfied($action));
    }

    #[Test]
    public function is_satisfied_checks_action_event_via_complete_action_storage_success(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: fn(): stdClass => new stdClass(),
            onOne: 'SourceAction::Success',
        );

        $sourceAction = new Action(
            id: 'SourceAction',
            externalId: 'SourceAction',
            handler: fn(): stdClass => new stdClass(),
        );

        $completeAction = new CompleteAction(
            action: $sourceAction,
            result: Result::success(new stdClass()),
            taskId: 'task-1',
        );

        $this->eventRelationStorage
            ->expects($this->once())
            ->method('isExists')
            ->with('SourceAction::Success')
            ->willReturn(false);

        $this->completeActionStorage
            ->expects($this->once())
            ->method('isExists')
            ->with('SourceAction')
            ->willReturn(true);

        $this->completeActionStorage
            ->expects($this->once())
            ->method('get')
            ->with('SourceAction')
            ->willReturn($completeAction);

        $this->assertTrue($this->subscriptionChecker->isSatisfied($action));
    }

    #[Test]
    public function is_satisfied_checks_action_event_via_complete_action_storage_fail(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: fn(): stdClass => new stdClass(),
            onOne: 'SourceAction::Fail',
        );

        $sourceAction = new Action(
            id: 'SourceAction',
            externalId: 'SourceAction',
            handler: fn(): stdClass => new stdClass(),
        );

        $completeAction = new CompleteAction(
            action: $sourceAction,
            result: Result::fail(),
            taskId: 'task-1',
        );

        $this->eventRelationStorage
            ->expects($this->once())
            ->method('isExists')
            ->with('SourceAction::Fail')
            ->willReturn(false);

        $this->completeActionStorage
            ->expects($this->once())
            ->method('isExists')
            ->with('SourceAction')
            ->willReturn(true);

        $this->completeActionStorage
            ->expects($this->once())
            ->method('get')
            ->with('SourceAction')
            ->willReturn($completeAction);

        $this->assertTrue($this->subscriptionChecker->isSatisfied($action));
    }

    #[Test]
    public function is_satisfied_returns_false_when_action_not_complete(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: fn(): stdClass => new stdClass(),
            onOne: 'SourceAction::Success',
        );

        $this->eventRelationStorage
            ->expects($this->once())
            ->method('isExists')
            ->with('SourceAction::Success')
            ->willReturn(false);

        $this->completeActionStorage
            ->expects($this->once())
            ->method('isExists')
            ->with('SourceAction')
            ->willReturn(false);

        $this->assertFalse($this->subscriptionChecker->isSatisfied($action));
    }

    #[Test]
    public function is_satisfied_returns_false_for_event_without_status(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: fn(): stdClass => new stdClass(),
            onOne: 'SimpleEventId',
        );

        $this->eventRelationStorage
            ->expects($this->once())
            ->method('isExists')
            ->with('SimpleEventId')
            ->willReturn(false);

        $this->assertFalse($this->subscriptionChecker->isSatisfied($action));
    }

    #[Test]
    public function is_satisfied_with_multiple_subscription_types_on_any(): void
    {
        $sourceAction1 = new Action(
            id: 'Action1',
            externalId: 'Action1',
            handler: fn(): stdClass => new stdClass(),
        );

        $sourceAction2 = new Action(
            id: 'Action2',
            externalId: 'Action2',
            handler: fn(): stdClass => new stdClass(),
        );

        $completeAction1 = new CompleteAction(
            action: $sourceAction1,
            result: Result::success(new stdClass()),
            taskId: 'task-1',
        );

        $completeAction2 = new CompleteAction(
            action: $sourceAction2,
            result: Result::fail(),
            taskId: 'task-2',
        );

        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: fn(): stdClass => new stdClass(),
            onAny: ['Action1::Success', 'Action2::Fail'],
        );

        $this->eventRelationStorage
            ->expects($this->once())
            ->method('isExists')
            ->with('Action1::Success')
            ->willReturn(false);

        $this->completeActionStorage
            ->expects($this->once())
            ->method('isExists')
            ->with('Action1')
            ->willReturn(true);

        $this->completeActionStorage
            ->expects($this->once())
            ->method('get')
            ->with('Action1')
            ->willReturn($completeAction1);

        $this->assertTrue($this->subscriptionChecker->isSatisfied($action));
    }

    #[Test]
    public function is_satisfied_with_on_all_mixed_events(): void
    {
        $sourceAction = new Action(
            id: 'Action1',
            externalId: 'Action1',
            handler: fn(): stdClass => new stdClass(),
        );

        $completeAction = new CompleteAction(
            action: $sourceAction,
            result: Result::success(new stdClass()),
            taskId: 'task-1',
        );

        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: fn(): stdClass => new stdClass(),
            onAll: ['ExternalEvent', 'Action1::Success'],
        );

        $this->eventRelationStorage
            ->expects($this->exactly(2))
            ->method('isExists')
            ->willReturnMap([
                ['ExternalEvent', true],
                ['Action1::Success', false],
            ]);

        $this->completeActionStorage
            ->expects($this->once())
            ->method('isExists')
            ->with('Action1')
            ->willReturn(true);

        $this->completeActionStorage
            ->expects($this->once())
            ->method('get')
            ->with('Action1')
            ->willReturn($completeAction);

        $this->assertTrue($this->subscriptionChecker->isSatisfied($action));
    }
}
