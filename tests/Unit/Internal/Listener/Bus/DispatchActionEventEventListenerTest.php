<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Internal\Listener\Bus;

use Duyler\EventBus\Bus\Action;
use Duyler\EventBus\Bus\Task;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Internal\Listener\Bus\DispatchActionEventEventListener;
use Duyler\EventBus\Service\EventService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

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
        $task = new Task($action, 'common');

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

        $this->assertSame('MyAction', $capturedEventId);
    }
}
