<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Internal\Listener\Bus;

use Duyler\EventBus\Bus\Actor;
use Duyler\EventBus\Bus\Task;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Internal\Listener\Bus\DispatchActorEventEventListener;
use Duyler\EventBus\Service\EventService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final readonly class TestActorData
{
    public function __construct(
        public string $value = '',
    ) {}
}

class DispatchActorEventEventListenerTest extends TestCase
{
    private EventService&MockObject $eventService;

    private DispatchActorEventEventListener $listener;

    protected function setUp(): void
    {
        $this->eventService = $this->createMock(EventService::class);
        $this->listener = new DispatchActorEventEventListener($this->eventService);
    }

    private function createTaskWithResult(Actor $actor, Result $result): Task
    {
        $task = new Task($actor, 'common');

        $reflection = new ReflectionProperty(Task::class, 'result');
        $reflection->setValue($task, $result);

        return $task;
    }

    #[Test]
    public function skip_silent_actor(): void
    {
        $actor = new Actor(
            id: 'silent-actor',
            externalId: 'silent-actor',
            handler: 'handler',
            silent: true,
        );

        $task = $this->createTaskWithResult($actor, Result::success());

        $this->eventService
            ->expects($this->never())
            ->method('dispatchActorEvent');

        $event = new TaskAfterRunEvent($task);
        ($this->listener)($event);
    }

    #[Test]
    #[AllowMockObjectsWithoutExpectations]
    public function composite_id_format(): void
    {
        $actorId = 'MyActor';
        $actor = new Actor(
            id: $actorId,
            externalId: $actorId,
            handler: 'handler',
        );

        $result = Result::success();

        $task = $this->createTaskWithResult($actor, $result);

        $capturedEventId = '';

        $this->eventService
            ->method('dispatchActorEvent')
            ->willReturnCallback(function (string $eventId) use (&$capturedEventId): void {
                $capturedEventId = $eventId;
            });

        $event = new TaskAfterRunEvent($task);
        ($this->listener)($event);

        $this->assertSame('MyActor', $capturedEventId);
    }
}
