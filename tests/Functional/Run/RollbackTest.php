<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\RollbackActionInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RollbackTest extends TestCase
{
    #[Test]
    public function run_with_rollback_closure()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: function () {throw new RuntimeException('Test error with closure'); },
                rollback: function () {},
                externalAccess: true,
            )
        );

        $bud = $busBuilder->build();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Test error with closure');
        $bud->run();
    }

    #[Test]
    public function run_with_rollback_class()
    {
        $busBuilder = new BusBuilder(new BusConfig());
        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: function () {throw new RuntimeException('Test error with class'); },
                rollback: Rollback::class,
                externalAccess: true,
            )
        );

        $bud = $busBuilder->build();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Test error with class');
        $bud->run();
    }
}

class Rollback implements RollbackActionInterface
{
    #[Override]
    public function run(Result $result): void {}
}
