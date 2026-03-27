<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Storage;

use Duyler\EventBus\Bus\Actor;
use Duyler\EventBus\Bus\CompleteActor;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Storage\CompleteActorStorage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EventStorageTest extends TestCase
{
    private CompleteActorStorage $eventStorage;

    #[Test]
    public function save_event(): void
    {
        $actor = new Actor(id: 'test', externalId: 'Empty.Required.Actor', handler: 'test');
        $event = new CompleteActor(
            actor: $actor,
            result: Result::success(),
            taskId: 'taskId',
            scope: 'common',
        );

        $this->eventStorage->save($event);

        $this->assertEquals($event, $this->eventStorage->get($event->actor->getId()));
        $this->assertTrue($this->eventStorage->isExists($event->actor->getId()));
        $this->assertEquals($event->result, $this->eventStorage->getResult($event->actor->getId()));
    }

    protected function setUp(): void
    {
        $this->eventStorage = new CompleteActorStorage();
    }
}
