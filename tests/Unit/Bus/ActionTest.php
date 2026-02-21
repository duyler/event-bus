<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Bus;

use Duyler\EventBus\Build\Action as ExternalAction;
use Duyler\EventBus\Build\Id;
use Duyler\EventBus\Bus\Action;
use Duyler\EventBus\Enum\SubscriptionType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class ActionTest extends TestCase
{
    #[Test]
    public function from_external_with_on_one_string(): void
    {
        $externalAction = new ExternalAction(
            id: 'TestAction',
            handler: fn(): stdClass => new stdClass(),
            onOne: 'Event.Success',
        );

        $action = Action::fromExternal($externalAction);

        $this->assertSame('Event.Success', $action->getOnOne());
        $this->assertSame([], $action->getOnAny());
        $this->assertSame([], $action->getOnAll());
        $this->assertSame(SubscriptionType::One, $action->getSubscriptionType());
        $this->assertSame(['Event.Success'], $action->getSubscriptionEvents());
    }

    #[Test]
    public function from_external_with_on_one_id_object(): void
    {
        $id = Id::success('SourceAction');

        $externalAction = new ExternalAction(
            id: 'TestAction',
            handler: fn(): stdClass => new stdClass(),
            onOne: $id,
        );

        $action = Action::fromExternal($externalAction);

        $this->assertSame((string) $id, $action->getOnOne());
        $this->assertSame(SubscriptionType::One, $action->getSubscriptionType());
        $this->assertSame([(string) $id], $action->getSubscriptionEvents());
    }

    #[Test]
    public function from_external_with_on_any(): void
    {
        $externalAction = new ExternalAction(
            id: 'TestAction',
            handler: fn(): stdClass => new stdClass(),
            onAny: ['Event1.Success', 'Event2.Success'],
        );

        $action = Action::fromExternal($externalAction);

        $this->assertNull($action->getOnOne());
        $this->assertSame(['Event1.Success', 'Event2.Success'], $action->getOnAny());
        $this->assertSame([], $action->getOnAll());
        $this->assertSame(SubscriptionType::Any, $action->getSubscriptionType());
        $this->assertSame(['Event1.Success', 'Event2.Success'], $action->getSubscriptionEvents());
    }

    #[Test]
    public function from_external_with_on_any_id_objects(): void
    {
        $id1 = Id::success('Action1');
        $id2 = Id::fail('Action2');

        $externalAction = new ExternalAction(
            id: 'TestAction',
            handler: fn(): stdClass => new stdClass(),
            onAny: [$id1, $id2],
        );

        $action = Action::fromExternal($externalAction);

        $this->assertSame([(string) $id1, (string) $id2], $action->getOnAny());
        $this->assertSame(SubscriptionType::Any, $action->getSubscriptionType());
    }

    #[Test]
    public function from_external_with_on_all(): void
    {
        $externalAction = new ExternalAction(
            id: 'TestAction',
            handler: fn(): stdClass => new stdClass(),
            onAll: ['Event1.Success', 'Event2.Success', 'Event3.Success'],
        );

        $action = Action::fromExternal($externalAction);

        $this->assertNull($action->getOnOne());
        $this->assertSame([], $action->getOnAny());
        $this->assertSame(['Event1.Success', 'Event2.Success', 'Event3.Success'], $action->getOnAll());
        $this->assertSame(SubscriptionType::All, $action->getSubscriptionType());
        $this->assertSame(['Event1.Success', 'Event2.Success', 'Event3.Success'], $action->getSubscriptionEvents());
    }

    #[Test]
    public function from_external_with_on_all_id_objects(): void
    {
        $id1 = Id::success('Action1');
        $id2 = Id::fail('Action2');

        $externalAction = new ExternalAction(
            id: 'TestAction',
            handler: fn(): stdClass => new stdClass(),
            onAll: [$id1, $id2],
        );

        $action = Action::fromExternal($externalAction);

        $this->assertSame([(string) $id1, (string) $id2], $action->getOnAll());
        $this->assertSame(SubscriptionType::All, $action->getSubscriptionType());
    }

    #[Test]
    public function from_external_without_subscription(): void
    {
        $externalAction = new ExternalAction(
            id: 'TestAction',
            handler: fn(): stdClass => new stdClass(),
        );

        $action = Action::fromExternal($externalAction);

        $this->assertNull($action->getOnOne());
        $this->assertSame([], $action->getOnAny());
        $this->assertSame([], $action->getOnAll());
        $this->assertSame(SubscriptionType::None, $action->getSubscriptionType());
        $this->assertSame([], $action->getSubscriptionEvents());
    }

    #[Test]
    public function get_subscription_type_returns_one_when_on_one_set(): void
    {
        $externalAction = new ExternalAction(
            id: 'TestAction',
            handler: fn(): stdClass => new stdClass(),
            onOne: 'Event.Success',
        );

        $action = Action::fromExternal($externalAction);

        $this->assertSame(SubscriptionType::One, $action->getSubscriptionType());
    }

    #[Test]
    public function get_subscription_type_returns_any_when_on_any_not_empty(): void
    {
        $externalAction = new ExternalAction(
            id: 'TestAction',
            handler: fn(): stdClass => new stdClass(),
            onAny: ['Event.Success'],
        );

        $action = Action::fromExternal($externalAction);

        $this->assertSame(SubscriptionType::Any, $action->getSubscriptionType());
    }

    #[Test]
    public function get_subscription_type_returns_all_when_on_all_not_empty(): void
    {
        $externalAction = new ExternalAction(
            id: 'TestAction',
            handler: fn(): stdClass => new stdClass(),
            onAll: ['Event.Success'],
        );

        $action = Action::fromExternal($externalAction);

        $this->assertSame(SubscriptionType::All, $action->getSubscriptionType());
    }

    #[Test]
    public function get_subscription_type_returns_none_when_no_subscription(): void
    {
        $externalAction = new ExternalAction(
            id: 'TestAction',
            handler: fn(): stdClass => new stdClass(),
        );

        $action = Action::fromExternal($externalAction);

        $this->assertSame(SubscriptionType::None, $action->getSubscriptionType());
    }

    #[Test]
    public function get_subscription_events_returns_single_event_for_on_one(): void
    {
        $externalAction = new ExternalAction(
            id: 'TestAction',
            handler: fn(): stdClass => new stdClass(),
            onOne: 'Event.Success',
        );

        $action = Action::fromExternal($externalAction);

        $this->assertSame(['Event.Success'], $action->getSubscriptionEvents());
    }

    #[Test]
    public function get_subscription_events_returns_multiple_events_for_on_any(): void
    {
        $externalAction = new ExternalAction(
            id: 'TestAction',
            handler: fn(): stdClass => new stdClass(),
            onAny: ['Event1.Success', 'Event2.Success'],
        );

        $action = Action::fromExternal($externalAction);

        $this->assertSame(['Event1.Success', 'Event2.Success'], $action->getSubscriptionEvents());
    }

    #[Test]
    public function get_subscription_events_returns_all_events_for_on_all(): void
    {
        $externalAction = new ExternalAction(
            id: 'TestAction',
            handler: fn(): stdClass => new stdClass(),
            onAll: ['Event1.Success', 'Event2.Success'],
        );

        $action = Action::fromExternal($externalAction);

        $this->assertSame(['Event1.Success', 'Event2.Success'], $action->getSubscriptionEvents());
    }

    #[Test]
    public function get_subscription_events_returns_empty_array_for_no_subscription(): void
    {
        $externalAction = new ExternalAction(
            id: 'TestAction',
            handler: fn(): stdClass => new stdClass(),
        );

        $action = Action::fromExternal($externalAction);

        $this->assertSame([], $action->getSubscriptionEvents());
    }
}
