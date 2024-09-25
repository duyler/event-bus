<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\RollbackActionInterface;
use Duyler\EventBus\Dto\Rollback as RollbackDto;
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
        $busBuilder->addAction(
            new Action(
                id: 'TestRollback',
                handler: function () {},
                rollback: Rollback::class,
            ),
        );

        $busBuilder->doAction(
            new Action(
                id: 'Test',
                handler: function () {throw new RuntimeException('Test error with class'); },
                required: ['TestRollback'],
            ),
        );

        $bud = $busBuilder->build();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Test error with class');
        $bud->run();
    }

    #[Test]
    public function run_with_rollback_after_action_flush()
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->addAction(
            new Action(
                id: 'Test1',
                handler: function () {},
                required: ['Test2'],
                rollback: function (RollbackDto $rollback) {
                    $rollback->action;
                    $rollback->container;
                    $rollback->argument;
                    $rollback->result;
                },
            ),
        );

        $busBuilder->addAction(
            new Action(
                id: 'Test2',
                handler: function () {},
                rollback: function () {},
                flush: true,
            ),
        );

        $busBuilder->doAction(
            new Action(
                id: 'TestWithFlush',
                handler: function () {throw new RuntimeException('Test error with closure'); },
                required: ['Test1'],
                rollback: function () {},
            ),
        );

        $bud = $busBuilder->build();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Test error with closure');
        $bud->run();
    }
}

class Rollback implements RollbackActionInterface
{
    #[Override]
    public function run(RollbackDto $rollback): void
    {
        $rollback->action;
        $rollback->container;
        $rollback->argument;
        $rollback->result;
    }
}
