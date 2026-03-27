<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Functional\Run;

use Duyler\EventBus\Build\Actor;
use Duyler\EventBus\BusBuilder;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\RollbackActorInterface;
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
        $busBuilder->doActor(
            new Actor(
                id: 'Test',
                handler: function (): void {
                    throw new RuntimeException('Test error with closure');
                },
                rollback: function (): void {},
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
        $busBuilder->addActor(
            new Actor(
                id: 'TestRollback',
                handler: function (): void {},
                rollback: Rollback::class,
            ),
        );

        $busBuilder->doActor(
            new Actor(
                id: 'Test',
                handler: function (): void {
                    throw new RuntimeException('Test error with class');
                },
                required: ['TestRollback'],
            ),
        );

        $bud = $busBuilder->build();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Test error with class');
        $bud->run();
    }

    #[Test]
    public function run_with_rollback_after_actor_flush()
    {
        $busBuilder = new BusBuilder(new BusConfig());

        $busBuilder->addActor(
            new Actor(
                id: 'Test1',
                handler: function (): void {},
                required: ['Test2'],
                rollback: function (RollbackDto $rollback): void {
                    $rollback->actor;
                    $rollback->container;
                    $rollback->argument;
                    $rollback->result;
                },
            ),
        );

        $busBuilder->addActor(
            new Actor(
                id: 'Test2',
                handler: function (): void {},
                rollback: function (): void {},
            ),
        );

        $busBuilder->doActor(
            new Actor(
                id: 'TestWithFlush',
                handler: function (): void {
                    throw new RuntimeException('Test error with closure');
                },
                required: ['Test1'],
                rollback: function (): void {},
            ),
        );

        $bud = $busBuilder->build();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Test error with closure');
        $bud->run();
    }
}

class Rollback implements RollbackActorInterface
{
    #[Override]
    public function run(RollbackDto $rollback): void
    {
        $rollback->actor;
        $rollback->container;
        $rollback->argument;
        $rollback->result;
    }
}
