<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Storage;

use Duyler\EventBus\Bus\Action;
use Duyler\EventBus\Bus\CompleteAction;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Storage\CompleteActionStorage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EventStorageTest extends TestCase
{
    private CompleteActionStorage $eventStorage;

    #[Test]
    public function save_event(): void
    {
        $action = new Action(id: 'test', externalId: 'Empty.Required.Action', handler: 'test');
        $event = new CompleteAction(
            action: $action,
            result: Result::success(),
            taskId: 'taskId',
        );

        $this->eventStorage->save($event);

        $this->assertEquals($event, $this->eventStorage->get($event->action->id));
        $this->assertTrue($this->eventStorage->isExists($event->action->id));
        $this->assertEquals($event->result, $this->eventStorage->getResult($event->action->id));
    }

    protected function setUp(): void
    {
        $this->eventStorage = new CompleteActionStorage();
    }
}
