<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\unit\Bus;

use Duyler\EventBus\Bus\DoWhile;
use Duyler\EventBus\Bus\Rollback;
use Duyler\EventBus\Runner;
use Duyler\EventBus\Service\ResultService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class RunnerTest extends TestCase
{
    private DoWhile $doWhile;
    private Rollback $rollback;
    private ResultService $resultService;
    private Runner $runner;

    #[Test]
    public function get_result_on_empty_bus(): void
    {
        $this->resultService->method('resultIsExists')->willReturn(false);
        $this->assertEquals(null, $this->runner->getResult('Action'));
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->doWhile = $this->createMock(DoWhile::class);
        $this->rollback = $this->createMock(Rollback::class);
        $this->resultService = $this->createMock(ResultService::class);

        $this->runner = new Runner(
            doWhile: $this->doWhile,
            rollback: $this->rollback,
            resultService: $this->resultService,
        );
        parent::setUp();
    }
}
