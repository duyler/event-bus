<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Build;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\Id;
use Duyler\EventBus\Enum\SubscriptionType;
use Duyler\EventBus\Exception\InvalidSubscriptionCombinationException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class ActionTest extends TestCase
{
    #[Test]
    public function creates_action_with_on_one_string(): void
    {
        $action = new Action(
            id: 'TestAction',
            handler: fn() => new stdClass(),
            onOne: 'TestEvent.Success',
        );

        $this->assertSame('TestAction', $action->id);
        $this->assertSame('TestEvent.Success', $action->onOne);
        $this->assertSame([], $action->onAny);
        $this->assertSame([], $action->onAll);
    }

    #[Test]
    public function creates_action_with_on_one_id_object(): void
    {
        $id = Id::success('SourceAction');

        $action = new Action(
            id: 'TestAction',
            handler: fn() => new stdClass(),
            onOne: $id,
        );

        $this->assertSame($id, $action->onOne);
    }

    #[Test]
    public function creates_action_with_on_any_empty(): void
    {
        $action = new Action(
            id: 'TestAction',
            handler: fn() => new stdClass(),
            onAny: [],
        );

        $this->assertNull($action->onOne);
        $this->assertSame([], $action->onAny);
        $this->assertSame([], $action->onAll);
    }

    #[Test]
    public function creates_action_with_on_any_with_elements(): void
    {
        $action = new Action(
            id: 'TestAction',
            handler: fn() => new stdClass(),
            onAny: ['Event1.Success', 'Event2.Success'],
        );

        $this->assertNull($action->onOne);
        $this->assertSame(['Event1.Success', 'Event2.Success'], $action->onAny);
        $this->assertSame([], $action->onAll);
    }

    #[Test]
    public function creates_action_with_on_any_with_id_objects(): void
    {
        $id1 = Id::success('Action1');
        $id2 = Id::success('Action2');

        $action = new Action(
            id: 'TestAction',
            handler: fn() => new stdClass(),
            onAny: [$id1, $id2],
        );

        $this->assertSame([$id1, $id2], $action->onAny);
    }

    #[Test]
    public function creates_action_with_on_all_empty(): void
    {
        $action = new Action(
            id: 'TestAction',
            handler: fn() => new stdClass(),
            onAll: [],
        );

        $this->assertNull($action->onOne);
        $this->assertSame([], $action->onAny);
        $this->assertSame([], $action->onAll);
    }

    #[Test]
    public function creates_action_with_on_all_with_elements(): void
    {
        $action = new Action(
            id: 'TestAction',
            handler: fn() => new stdClass(),
            onAll: ['Event1.Success', 'Event2.Success', 'Event3.Success'],
        );

        $this->assertNull($action->onOne);
        $this->assertSame([], $action->onAny);
        $this->assertSame(['Event1.Success', 'Event2.Success', 'Event3.Success'], $action->onAll);
    }

    #[Test]
    public function creates_action_with_on_all_with_id_objects(): void
    {
        $id1 = Id::success('Action1');
        $id2 = Id::success('Action2');

        $action = new Action(
            id: 'TestAction',
            handler: fn() => new stdClass(),
            onAll: [$id1, $id2],
        );

        $this->assertSame([$id1, $id2], $action->onAll);
    }

    #[Test]
    public function creates_action_without_subscription(): void
    {
        $action = new Action(
            id: 'TestAction',
            handler: fn() => new stdClass(),
        );

        $this->assertNull($action->onOne);
        $this->assertSame([], $action->onAny);
        $this->assertSame([], $action->onAll);
    }

    #[Test]
    public function throws_exception_for_on_one_and_on_any_combination(): void
    {
        $this->expectException(InvalidSubscriptionCombinationException::class);

        new Action(
            id: 'TestAction',
            handler: fn() => new stdClass(),
            onOne: 'Event1.Success',
            onAny: ['Event2.Success'],
        );
    }

    #[Test]
    public function throws_exception_for_on_one_and_on_all_combination(): void
    {
        $this->expectException(InvalidSubscriptionCombinationException::class);

        new Action(
            id: 'TestAction',
            handler: fn() => new stdClass(),
            onOne: 'Event1.Success',
            onAll: ['Event2.Success'],
        );
    }

    #[Test]
    public function throws_exception_for_on_any_and_on_all_combination(): void
    {
        $this->expectException(InvalidSubscriptionCombinationException::class);

        new Action(
            id: 'TestAction',
            handler: fn() => new stdClass(),
            onAny: ['Event1.Success'],
            onAll: ['Event2.Success'],
        );
    }

    #[Test]
    public function throws_exception_for_all_three_subscription_types(): void
    {
        $this->expectException(InvalidSubscriptionCombinationException::class);

        new Action(
            id: 'TestAction',
            handler: fn() => new stdClass(),
            onOne: 'Event1.Success',
            onAny: ['Event2.Success'],
            onAll: ['Event3.Success'],
        );
    }

    #[Test]
    public function exception_contains_subscription_types(): void
    {
        try {
            new Action(
                id: 'TestAction',
                handler: fn() => new stdClass(),
                onOne: 'Event1.Success',
                onAny: ['Event2.Success'],
            );
        } catch (InvalidSubscriptionCombinationException $e) {
            $this->assertStringContainsString(SubscriptionType::One->name, $e->getMessage());
            $this->assertStringContainsString(SubscriptionType::Any->name, $e->getMessage());
        }
    }

    #[Test]
    public function json_serialize_contains_on_one(): void
    {
        $action = new Action(
            id: 'TestAction',
            handler: fn() => new stdClass(),
            onOne: 'TestEvent.Success',
        );

        $serialized = $action->jsonSerialize();

        $this->assertArrayHasKey('onOne', $serialized);
        $this->assertSame('TestEvent.Success', $serialized['onOne']);
        $this->assertArrayHasKey('onAny', $serialized);
        $this->assertSame([], $serialized['onAny']);
        $this->assertArrayHasKey('onAll', $serialized);
        $this->assertSame([], $serialized['onAll']);
    }

    #[Test]
    public function json_serialize_contains_on_any(): void
    {
        $action = new Action(
            id: 'TestAction',
            handler: fn() => new stdClass(),
            onAny: ['Event1.Success', 'Event2.Success'],
        );

        $serialized = $action->jsonSerialize();

        $this->assertNull($serialized['onOne']);
        $this->assertSame(['Event1.Success', 'Event2.Success'], $serialized['onAny']);
        $this->assertSame([], $serialized['onAll']);
    }

    #[Test]
    public function json_serialize_contains_on_all(): void
    {
        $action = new Action(
            id: 'TestAction',
            handler: fn() => new stdClass(),
            onAll: ['Event1.Success', 'Event2.Success'],
        );

        $serialized = $action->jsonSerialize();

        $this->assertNull($serialized['onOne']);
        $this->assertSame([], $serialized['onAny']);
        $this->assertSame(['Event1.Success', 'Event2.Success'], $serialized['onAll']);
    }

    #[Test]
    public function json_serialize_converts_id_to_string(): void
    {
        $id = Id::success('SourceAction');

        $action = new Action(
            id: 'TestAction',
            handler: fn() => new stdClass(),
            onOne: $id,
        );

        $serialized = $action->jsonSerialize();

        $this->assertSame((string) $id, $serialized['onOne']);
    }

    #[Test]
    public function json_serialize_does_not_contain_listen(): void
    {
        $action = new Action(
            id: 'TestAction',
            handler: fn() => new stdClass(),
            onOne: 'TestEvent.Success',
        );

        $serialized = $action->jsonSerialize();

        $this->assertArrayNotHasKey('listen', $serialized);
    }
}
