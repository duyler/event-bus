<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Storage;

use Duyler\EventBus\Bus\Action;
use Duyler\EventBus\Storage\ActionStorage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ActionStorageTest extends TestCase
{
    private ActionStorage $actionStorage;

    protected function setUp(): void
    {
        $this->actionStorage = new ActionStorage();
    }

    #[Test]
    public function save_action(): void
    {
        $action = new Action(
            id: 'test',
            externalId: 'test',
            handler: 'test',
            type: 'test',
            immutable: false,
        );
        $this->actionStorage->save($action);

        $this->assertSame($action, $this->actionStorage->get($action->getId()));
    }

    #[Test]
    public function get_by_subscription_event_with_on_one(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: 'handler',
            onOne: 'event-1',
        );
        $this->actionStorage->save($action);

        $actions = $this->actionStorage->getBySubscriptionEvent('event-1');

        $this->assertArrayHasKey('test-action', $actions);
        $this->assertSame($action, $actions['test-action']);
    }

    #[Test]
    public function get_by_subscription_event_with_on_any(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: 'handler',
            onAny: ['event-1', 'event-2', 'event-3'],
        );
        $this->actionStorage->save($action);

        $actionsEvent1 = $this->actionStorage->getBySubscriptionEvent('event-1');
        $actionsEvent2 = $this->actionStorage->getBySubscriptionEvent('event-2');
        $actionsEvent3 = $this->actionStorage->getBySubscriptionEvent('event-3');

        $this->assertArrayHasKey('test-action', $actionsEvent1);
        $this->assertArrayHasKey('test-action', $actionsEvent2);
        $this->assertArrayHasKey('test-action', $actionsEvent3);
        $this->assertSame($action, $actionsEvent1['test-action']);
        $this->assertSame($action, $actionsEvent2['test-action']);
        $this->assertSame($action, $actionsEvent3['test-action']);
    }

    #[Test]
    public function get_by_subscription_event_with_on_all(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: 'handler',
            onAll: ['event-1', 'event-2'],
        );
        $this->actionStorage->save($action);

        $actionsEvent1 = $this->actionStorage->getBySubscriptionEvent('event-1');
        $actionsEvent2 = $this->actionStorage->getBySubscriptionEvent('event-2');

        $this->assertArrayHasKey('test-action', $actionsEvent1);
        $this->assertArrayHasKey('test-action', $actionsEvent2);
        $this->assertSame($action, $actionsEvent1['test-action']);
        $this->assertSame($action, $actionsEvent2['test-action']);
    }

    #[Test]
    public function get_by_subscription_event_returns_empty_for_non_existent_event(): void
    {
        $actions = $this->actionStorage->getBySubscriptionEvent('non-existent-event');

        $this->assertSame([], $actions);
    }

    #[Test]
    public function remove_clears_subscription_event_indexes(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: 'handler',
            onAny: ['event-1', 'event-2'],
        );
        $this->actionStorage->save($action);
        $this->actionStorage->remove('test-action');

        $actionsEvent1 = $this->actionStorage->getBySubscriptionEvent('event-1');
        $actionsEvent2 = $this->actionStorage->getBySubscriptionEvent('event-2');

        $this->assertArrayNotHasKey('test-action', $actionsEvent1);
        $this->assertArrayNotHasKey('test-action', $actionsEvent2);
        $this->assertFalse($this->actionStorage->isExists('test-action'));
    }

    #[Test]
    public function remove_action_without_type(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: 'handler',
        );
        $this->actionStorage->save($action);
        $this->actionStorage->remove('test-action');

        $this->assertFalse($this->actionStorage->isExists('test-action'));
    }

    #[Test]
    public function remove_non_existent_action_does_not_throw(): void
    {
        $this->actionStorage->remove('non-existent');

        $this->assertFalse($this->actionStorage->isExists('non-existent'));
    }

    #[Test]
    public function reset_clears_all_data(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: 'handler',
            onOne: 'event-1',
        );
        $this->actionStorage->save($action);
        $this->actionStorage->reset();

        $this->assertFalse($this->actionStorage->isExists('test-action'));
        $this->assertSame([], $this->actionStorage->getBySubscriptionEvent('event-1'));
        $this->assertSame([], $this->actionStorage->getAll());
    }

    #[Test]
    public function multiple_actions_same_event(): void
    {
        $action1 = new Action(
            id: 'action-1',
            externalId: 'action-1',
            handler: 'handler',
            onOne: 'event-1',
        );
        $action2 = new Action(
            id: 'action-2',
            externalId: 'action-2',
            handler: 'handler',
            onOne: 'event-1',
        );
        $this->actionStorage->save($action1);
        $this->actionStorage->save($action2);

        $actions = $this->actionStorage->getBySubscriptionEvent('event-1');

        $this->assertCount(2, $actions);
        $this->assertArrayHasKey('action-1', $actions);
        $this->assertArrayHasKey('action-2', $actions);
    }

    #[Test]
    public function remove_one_action_keeps_others_in_index(): void
    {
        $action1 = new Action(
            id: 'action-1',
            externalId: 'action-1',
            handler: 'handler',
            onOne: 'event-1',
        );
        $action2 = new Action(
            id: 'action-2',
            externalId: 'action-2',
            handler: 'handler',
            onOne: 'event-1',
        );
        $this->actionStorage->save($action1);
        $this->actionStorage->save($action2);
        $this->actionStorage->remove('action-1');

        $actions = $this->actionStorage->getBySubscriptionEvent('event-1');

        $this->assertCount(1, $actions);
        $this->assertArrayNotHasKey('action-1', $actions);
        $this->assertArrayHasKey('action-2', $actions);
    }

    #[Test]
    public function action_without_subscription_returns_empty_events(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'test-action',
            handler: 'handler',
        );
        $this->actionStorage->save($action);

        $this->assertSame([], $action->getSubscriptionEvents());
        $this->assertSame([], $this->actionStorage->getBySubscriptionEvent('any-event'));
    }
}
