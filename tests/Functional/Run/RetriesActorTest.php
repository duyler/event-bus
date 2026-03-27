<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use DateInterval;
use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\ResultStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RetriesActorTest extends TestCase
{
    #[Test]
    public function retries(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doActor(
            new Actor(
                id: 'RetryActor',
                handler: fn(): Result => Result::fail(),
                repeatable: false,
                retries: 3,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();
        $this->assertTrue($bus->resultIsExists('RetryActor'));
        $this->assertEquals(ResultStatus::Fail, $bus->getResult('RetryActor')->status);
    }

    #[Test]
    public function retries_with_delay(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doActor(
            new Actor(
                id: 'RetryActor',
                handler: fn(): Result => Result::fail(),
                repeatable: false,
                retries: 1,
                retryDelay: DateInterval::createFromDateString('100 millisecond'),
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();
        $this->assertTrue($bus->resultIsExists('RetryActor'));
        $this->assertEquals(ResultStatus::Fail, $bus->getResult('RetryActor')->status);
    }
}
