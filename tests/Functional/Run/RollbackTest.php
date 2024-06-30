<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Functional\Run;

use Duyler\ActionBus\Build\Action;
use Duyler\ActionBus\BusBuilder;
use Duyler\ActionBus\BusConfig;
use Duyler\ActionBus\Contract\RollbackActionInterface;
use Duyler\ActionBus\Dto\Result;
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
            ),
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
            ),
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
    public function run(Result $result, object|null $argument): void {}
}
