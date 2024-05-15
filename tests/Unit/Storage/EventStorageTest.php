<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Unit\Storage;

use Duyler\ActionBus\Bus\CompleteAction;
use Duyler\ActionBus\Storage\CompleteActionStorage;
use Duyler\ActionBus\Dto\Action;
use Duyler\ActionBus\Dto\Result;
use Duyler\ActionBus\Enum\ResultStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EventStorageTest extends TestCase
{
    private CompleteActionStorage $eventStorage;

    #[Test]
    public function save_event(): void
    {
        $action = new Action(id: 'test', handler: 'test');
        $event = new CompleteAction(
            action: $action,
            result: new Result(status: ResultStatus::Success),
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
