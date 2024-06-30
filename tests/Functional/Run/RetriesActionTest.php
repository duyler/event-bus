<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Functional\Run;

use Duyler\ActionBus\Build\Action;
use Duyler\ActionBus\BusBuilder;
use Duyler\ActionBus\BusConfig;
use Duyler\ActionBus\Dto\Result;
use Duyler\ActionBus\Enum\ResultStatus;
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
                handler: fn(): Result => new Result(status: ResultStatus::Fail),
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
