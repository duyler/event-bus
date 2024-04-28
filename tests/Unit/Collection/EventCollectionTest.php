<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Unit\Collection;

use Duyler\ActionBus\Bus\CompleteAction;
use Duyler\ActionBus\Collection\CompleteActionCollection;
use Duyler\ActionBus\Dto\Action;
use Duyler\ActionBus\Dto\Result;
use Duyler\ActionBus\Enum\ResultStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EventCollectionTest extends TestCase
{
    private CompleteActionCollection $eventCollection;

    #[Test]
    public function save_event(): void
    {
        $action = new Action(id: 'test', handler: 'test');
        $event = new CompleteAction(
            action: $action,
            result: new Result(status: ResultStatus::Success),
        );

        $this->eventCollection->save($event);

        $this->assertEquals($event, $this->eventCollection->get($event->action->id));
        $this->assertTrue($this->eventCollection->isExists($event->action->id));
        $this->assertEquals($event->result, $this->eventCollection->getResult($event->action->id));
    }

    protected function setUp(): void
    {
        $this->eventCollection = new CompleteActionCollection();
    }
}
