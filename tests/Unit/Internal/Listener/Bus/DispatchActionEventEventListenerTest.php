<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Internal\Listener\Bus;

use Duyler\EventBus\Build\Event;
use Duyler\EventBus\Bus\Action;
use Duyler\EventBus\Bus\Task;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Internal\Listener\Bus\DispatchActionEventEventListener;
use Duyler\EventBus\Service\EventService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;

final readonly class TestActionData
{
    public function __construct(
        public string $value = '',
    ) {}
}

class DispatchActionEventEventListenerTest extends TestCase
{
    private EventService&MockObject $eventService;

    private DispatchActionEventEventListener $listener;

    protected function setUp(): void
    {
        $this->eventService = $this->createMock(EventService::class);
        $this->listener = new DispatchActionEventEventListener($this->eventService);
    }

    private function createTaskWithResult(Action $action, Result $result): Task
    {
        $task = new Task($action);

        $reflection = new ReflectionProperty(Task::class, 'result');
        $reflection->setValue($task, $result);

        return $task;
    }

    #[Test]
    public function skip_silent_action(): void
    {
        $action = new Action(
            id: 'silent-action',
            externalId: 'silent-action',
            handler: 'handler',
            silent: true,
        );

        $task = $this->createTaskWithResult($action, Result::success());

        $this->eventService
            ->expects($this->never())
            ->method('dispatchActionEvent');

        $event = new TaskAfterRunEvent($task);
        ($this->listener)($event);
    }

    #[Test]
    public function dispatch_event_with_success_result(): void
    {
        $actionData = new TestActionData('test-value');
        $actionId = 'test-action';

        $action = new Action(
            id: $actionId,
            externalId: $actionId,
            handler: 'handler',
            type: TestActionData::class,
            immutable: true,
        );

        $result = Result::success($actionData);

        $task = $this->createTaskWithResult($action, $result);

        $expectedEventId = $actionId . IdFormatter::DELIMITER . ResultStatus::Success->value;

        $this->eventService
            ->expects($this->once())
            ->method('dispatchActionEvent')
            ->with(
                $this->callback(fn(string $eventId): bool => $eventId === $expectedEventId),
                $this->callback(fn(?object $data): bool => $data === $actionData),
                $this->callback(fn(Event $event): bool => $event->id === $actionId . IdFormatter::DELIMITER . ResultStatus::Success->value
                    && $event->status === ResultStatus::Success
                    && $event->type === TestActionData::class
                    && $event->immutable === true),
            );

        $event = new TaskAfterRunEvent($task);
        ($this->listener)($event);
    }

    #[Test]
    public function dispatch_event_with_fail_result(): void
    {
        $actionId = 'fail-action';

        $action = new Action(
            id: $actionId,
            externalId: $actionId,
            handler: 'handler',
            type: null,
            immutable: false,
        );

        $result = Result::fail();

        $task = $this->createTaskWithResult($action, $result);

        $expectedEventId = $actionId . IdFormatter::DELIMITER . ResultStatus::Fail->value;

        $this->eventService
            ->expects($this->once())
            ->method('dispatchActionEvent')
            ->with(
                $this->callback(fn(string $eventId): bool => $eventId === $expectedEventId),
                $this->callback(fn(?object $data): bool => null === $data),
                $this->callback(fn(Event $event): bool => $event->id === $actionId . IdFormatter::DELIMITER . ResultStatus::Fail->value
                    && $event->status === ResultStatus::Fail
                    && null === $event->type
                    && $event->immutable === false),
            );

        $event = new TaskAfterRunEvent($task);
        ($this->listener)($event);
    }

    #[Test]
    public function composite_id_format(): void
    {
        $actionId = 'MyAction';
        $action = new Action(
            id: $actionId,
            externalId: $actionId,
            handler: 'handler',
        );

        $result = Result::success();

        $task = $this->createTaskWithResult($action, $result);

        $capturedEventId = '';

        $this->eventService
            ->method('dispatchActionEvent')
            ->willReturnCallback(function (string $eventId) use (&$capturedEventId): void {
                $capturedEventId = $eventId;
            });

        $event = new TaskAfterRunEvent($task);
        ($this->listener)($event);

        $this->assertSame('MyAction::Success', $capturedEventId);
    }

    #[Test]
    public function data_passed_for_success(): void
    {
        $actionData = new TestActionData('test-data');
        $action = new Action(
            id: 'action-with-data',
            externalId: 'action-with-data',
            handler: 'handler',
            type: TestActionData::class,
        );

        $result = Result::success($actionData);

        $task = $this->createTaskWithResult($action, $result);

        $capturedData = null;

        $this->eventService
            ->method('dispatchActionEvent')
            ->willReturnCallback(function (string $eventId, ?object $data) use (&$capturedData): void {
                $capturedData = $data;
            });

        $event = new TaskAfterRunEvent($task);
        ($this->listener)($event);

        $this->assertSame($actionData, $capturedData);
    }

    #[Test]
    public function null_passed_for_fail(): void
    {
        $action = new Action(
            id: 'fail-action-no-data',
            externalId: 'fail-action-no-data',
            handler: 'handler',
        );

        $result = Result::fail();

        $task = $this->createTaskWithResult($action, $result);

        $capturedData = new stdClass();

        $this->eventService
            ->method('dispatchActionEvent')
            ->willReturnCallback(function (string $eventId, ?object $data) use (&$capturedData): void {
                $capturedData = $data;
            });

        $event = new TaskAfterRunEvent($task);
        ($this->listener)($event);

        $this->assertNull($capturedData);
    }

    #[Test]
    public function event_created_with_correct_parameters(): void
    {
        $action = new Action(
            id: 'test-action',
            externalId: 'ExternalTestAction',
            handler: 'handler',
            type: TestActionData::class,
            immutable: true,
        );

        $result = Result::success(new TestActionData());

        $task = $this->createTaskWithResult($action, $result);

        $capturedEvent = null;

        $this->eventService
            ->method('dispatchActionEvent')
            ->willReturnCallback(function (string $eventId, ?object $data, Event $event) use (&$capturedEvent): void {
                $capturedEvent = $event;
            });

        $event = new TaskAfterRunEvent($task);
        ($this->listener)($event);

        $this->assertNotNull($capturedEvent);
        $this->assertInstanceOf(Event::class, $capturedEvent);
        assert($capturedEvent instanceof Event);
        $this->assertSame('ExternalTestAction::Success', $capturedEvent->id);
        $this->assertSame(ResultStatus::Success, $capturedEvent->status);
        $this->assertSame(TestActionData::class, $capturedEvent->type);
        $this->assertTrue($capturedEvent->immutable);
    }
}
