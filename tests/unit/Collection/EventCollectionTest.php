<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\unit\Collection;

use Duyler\EventBus\Bus\Event;
use Duyler\EventBus\Collection\EventCollection;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\ResultStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EventCollectionTest extends TestCase
{
    private EventCollection $eventCollection;

    #[Test]
    public function save_event(): void
    {
        $action = new Action(id: 'test', handler: 'test');
        $event = new Event(
            action: $action,
            result: new Result(status: ResultStatus::Success)
        );

        $this->eventCollection->save($event);

        $this->assertEquals($event, $this->eventCollection->get($event->action->id));
        $this->assertTrue($this->eventCollection->isExists($event->action->id));
        $this->assertEquals($event->result, $this->eventCollection->getResult($event->action->id));
    }

    protected function setUp(): void
    {
        $this->eventCollection = new EventCollection();
    }
}
