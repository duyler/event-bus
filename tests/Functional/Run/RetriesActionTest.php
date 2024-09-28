<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\ResultStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RetriesActionTest extends TestCase
{
    #[Test]
    public function retries(): void
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'RetryAction',
                handler: fn(): Result => Result::fail(),
                repeatable: false,
                retries: 3,
            ),
        );

        $bus = $busBuilder->build();
        $bus->run();
        $this->assertTrue($bus->resultIsExists('RetryAction'));
        $this->assertEquals(ResultStatus::Fail, $bus->getResult('RetryAction')->status);
    }
}
