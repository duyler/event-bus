<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Build;

use Countable;
use Duyler\EventBus\Build\Event;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Formatter\IdFormatter;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

final readonly class TestReadOnlyDTO
{
    public function __construct(public string $value) {}
}

class EventTest extends TestCase
{
    #[Test]
    public function constructor_creates_event_with_default_success_status(): void
    {
        $event = new Event('TestEvent');

        $this->assertSame('TestEvent' . IdFormatter::DELIMITER . 'Success', $event->id);
        $this->assertSame(ResultStatus::Success, $event->status);
    }

    #[Test]
    public function constructor_creates_event_with_fail_status(): void
    {
        $event = new Event('TestEvent', ResultStatus::Fail);

        $this->assertSame('TestEvent' . IdFormatter::DELIMITER . 'Fail', $event->id);
        $this->assertSame(ResultStatus::Fail, $event->status);
    }

    #[Test]
    public function constructor_creates_event_with_type(): void
    {
        $event = new Event('TestEvent', ResultStatus::Success, TestReadOnlyDTO::class);

        $this->assertSame('TestEvent' . IdFormatter::DELIMITER . 'Success', $event->id);
        $this->assertSame(TestReadOnlyDTO::class, $event->type);
    }

    #[Test]
    public function constructor_creates_event_with_mutable_flag(): void
    {
        $event = new Event('TestEvent', ResultStatus::Success, null, false);

        $this->assertFalse($event->immutable);
    }

    #[Test]
    public function constructor_creates_event_with_description(): void
    {
        $event = new Event('TestEvent', ResultStatus::Success, null, true, 'Test description');

        $this->assertSame('Test description', $event->description);
    }

    #[Test]
    public function success_creates_event_with_success_status(): void
    {
        $event = Event::success('OrderCreated');

        $this->assertSame('OrderCreated' . IdFormatter::DELIMITER . 'Success', $event->id);
        $this->assertSame(ResultStatus::Success, $event->status);
    }

    #[Test]
    public function success_creates_event_with_type(): void
    {
        $event = Event::success('OrderCreated', TestReadOnlyDTO::class);

        $this->assertSame('OrderCreated' . IdFormatter::DELIMITER . 'Success', $event->id);
        $this->assertSame(TestReadOnlyDTO::class, $event->type);
    }

    #[Test]
    public function fail_creates_event_with_fail_status(): void
    {
        $event = Event::fail('OrderCreated');

        $this->assertSame('OrderCreated' . IdFormatter::DELIMITER . 'Fail', $event->id);
        $this->assertSame(ResultStatus::Fail, $event->status);
    }

    #[Test]
    public function fail_creates_event_with_type(): void
    {
        $event = Event::fail('OrderCreated', TestReadOnlyDTO::class);

        $this->assertSame('OrderCreated' . IdFormatter::DELIMITER . 'Fail', $event->id);
        $this->assertSame(TestReadOnlyDTO::class, $event->type);
    }

    #[Test]
    public function constructor_accepts_enum_as_id(): void
    {
        $event = new Event(ResultStatus::Success);

        $this->assertStringContainsString('Success', $event->id);
    }

    #[Test]
    public function success_accepts_enum_as_id(): void
    {
        $event = Event::success(ResultStatus::Fail);

        $this->assertStringContainsString('Fail', $event->id);
        $this->assertStringContainsString('Success', $event->id);
    }

    #[Test]
    public function fail_accepts_enum_as_id(): void
    {
        $event = Event::fail(ResultStatus::Success);

        $this->assertStringContainsString('Success', $event->id);
        $this->assertStringContainsString('Fail', $event->id);
    }

    #[Test]
    public function constructor_throws_exception_for_interface_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('should not be an interface');

        new Event('TestEvent', ResultStatus::Success, Countable::class);
    }

    #[Test]
    public function constructor_throws_exception_for_non_readonly_class(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be read only class');

        new Event('TestEvent', ResultStatus::Success, stdClass::class);
    }

    #[Test]
    public function constructor_allows_non_readonly_class_when_immutable_is_false(): void
    {
        $event = new Event('TestEvent', ResultStatus::Success, stdClass::class, false);

        $this->assertSame(stdClass::class, $event->type);
        $this->assertFalse($event->immutable);
    }

    #[Test]
    public function json_serialize_includes_status(): void
    {
        $event = new Event('TestEvent', ResultStatus::Fail, null, true, 'Description');

        $json = $event->jsonSerialize();

        $this->assertSame('TestEvent' . IdFormatter::DELIMITER . 'Fail', $json['id']);
        $this->assertSame('Fail', $json['status']);
        $this->assertNull($json['type']);
        $this->assertTrue($json['immutable']);
        $this->assertSame('Description', $json['description']);
    }

    #[Test]
    public function json_serialize_with_type(): void
    {
        $event = new Event('TestEvent', ResultStatus::Success, TestReadOnlyDTO::class);

        $json = $event->jsonSerialize();

        $this->assertSame('TestEvent' . IdFormatter::DELIMITER . 'Success', $json['id']);
        $this->assertSame('Success', $json['status']);
        $this->assertSame(TestReadOnlyDTO::class, $json['type']);
    }
}
