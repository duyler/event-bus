<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Build;

use Duyler\EventBus\Build\Event;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AddEventTest extends TestCase
{
    #[Test]
    public function eventIsExists_with_action()
    {
        $builder = new BusBuilder(new BusConfig());

        $builder->addEvent(new Event(
            id: 'test',
        ));

        $this->assertTrue($builder->eventIsExists('test'));
    }
}
