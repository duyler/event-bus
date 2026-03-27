<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Scheduler;

use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\ScheduledTask;
use Duyler\EventBus\Scheduler\Scheduler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SchedulerTest extends TestCase
{
    private function createScheduledTask(
        callable $callback,
        int $intervalMs = 1000,
        ?int $startDelayMs = null,
    ): ScheduledTask {
        return new ScheduledTask(
            callback: $callback,
            intervalMs: $intervalMs,
            startDelayMs: $startDelayMs,
        );
    }

    private function createConfig(int $checkInterval = 100): BusConfig
    {
        return new BusConfig(
            schedulerCheckInterval: $checkInterval,
        );
    }

    #[Test]
    public function add_task_without_delay_sets_next_run_to_now(): void
    {
        $config = $this->createConfig(checkInterval: 10);
        $scheduler = new Scheduler($config);

        $executed = false;
        $task = $this->createScheduledTask(
            callback: function () use (&$executed): void {
                $executed = true;
            },
            intervalMs: 100,
            startDelayMs: null,
        );

        $scheduler->addTask($task);

        usleep(15_000);

        $scheduler->tick();

        $this->assertTrue($executed);
    }

    #[Test]
    public function add_task_with_delay_sets_next_run_in_future(): void
    {
        $config = $this->createConfig(checkInterval: 5);
        $scheduler = new Scheduler($config);

        $executed = false;
        $task = $this->createScheduledTask(
            callback: function () use (&$executed): void {
                $executed = true;
            },
            intervalMs: 100,
            startDelayMs: 100,
        );

        $scheduler->addTask($task);

        usleep(10_000);
        $scheduler->tick();

        $this->assertFalse($executed, 'Task should not execute before delay');

        usleep(100_000);
        $scheduler->tick();

        $this->assertTrue($executed, 'Task should execute after delay');
    }

    #[Test]
    public function add_task_stores_callback_correctly(): void
    {
        $config = $this->createConfig(checkInterval: 5);
        $scheduler = new Scheduler($config);

        $callbackExecuted = false;
        $callback = function () use (&$callbackExecuted): void {
            $callbackExecuted = true;
        };

        $task = $this->createScheduledTask(
            callback: $callback,
            intervalMs: 50,
        );

        $scheduler->addTask($task);

        usleep(10_000);
        $scheduler->tick();

        $this->assertTrue($callbackExecuted);
    }

    #[Test]
    public function add_task_stores_interval_correctly(): void
    {
        $config = $this->createConfig(checkInterval: 5);
        $scheduler = new Scheduler($config);

        $executionCount = 0;
        $task = $this->createScheduledTask(
            callback: function () use (&$executionCount): void {
                ++$executionCount;
            },
            intervalMs: 50,
        );

        $scheduler->addTask($task);

        usleep(60_000);
        $scheduler->tick();

        $firstCount = $executionCount;
        $this->assertSame(1, $firstCount);

        usleep(60_000);
        $scheduler->tick();

        $this->assertGreaterThan($firstCount, $executionCount);
    }

    #[Test]
    public function tick_before_check_interval_does_nothing(): void
    {
        $config = $this->createConfig(checkInterval: 1000);
        $scheduler = new Scheduler($config);

        $executed = false;
        $task = $this->createScheduledTask(
            callback: function () use (&$executed): void {
                $executed = true;
            },
            intervalMs: 1,
        );

        $scheduler->addTask($task);

        usleep(10_000);
        $scheduler->tick();

        $this->assertFalse($executed, 'Task should not execute before checkInterval');
    }

    #[Test]
    public function tick_after_check_interval_checks_tasks(): void
    {
        $config = $this->createConfig(checkInterval: 5);
        $scheduler = new Scheduler($config);

        $executed = false;
        $task = $this->createScheduledTask(
            callback: function () use (&$executed): void {
                $executed = true;
            },
            intervalMs: 1,
        );

        $scheduler->addTask($task);

        usleep(10_000);
        $scheduler->tick();

        $this->assertTrue($executed, 'Task should execute after checkInterval');
    }

    #[Test]
    public function tick_task_not_ready_does_not_execute(): void
    {
        $config = $this->createConfig(checkInterval: 5);
        $scheduler = new Scheduler($config);

        $executed = false;
        $task = $this->createScheduledTask(
            callback: function () use (&$executed): void {
                $executed = true;
            },
            intervalMs: 1000,
            startDelayMs: 500,
        );

        $scheduler->addTask($task);

        usleep(10_000);
        $scheduler->tick();

        $this->assertFalse($executed, 'Task should not execute before startDelay');
    }

    #[Test]
    public function tick_task_ready_executes_callback(): void
    {
        $config = $this->createConfig(checkInterval: 5);
        $scheduler = new Scheduler($config);

        $executed = false;
        $task = $this->createScheduledTask(
            callback: function () use (&$executed): void {
                $executed = true;
            },
            intervalMs: 100,
        );

        $scheduler->addTask($task);

        usleep(10_000);
        $scheduler->tick();

        $this->assertTrue($executed);
    }

    #[Test]
    public function tick_updates_last_run_after_execution(): void
    {
        $config = $this->createConfig(checkInterval: 5);
        $scheduler = new Scheduler($config);

        $task = $this->createScheduledTask(
            callback: function (): void {},
            intervalMs: 50,
        );

        $scheduler->addTask($task);

        usleep(10_000);
        $scheduler->tick();

        $stats = $scheduler->getStats();

        $this->assertNotNull($stats[0]['last_run_ago']);
    }

    #[Test]
    public function tick_updates_next_run_after_execution(): void
    {
        $config = $this->createConfig(checkInterval: 5);
        $scheduler = new Scheduler($config);

        $executionCount = 0;
        $task = $this->createScheduledTask(
            callback: function () use (&$executionCount): void {
                ++$executionCount;
            },
            intervalMs: 30,
        );

        $scheduler->addTask($task);

        usleep(10_000);
        $scheduler->tick();

        $this->assertSame(1, $executionCount);

        $statsAfterFirst = $scheduler->getStats();
        $nextRunIn = $statsAfterFirst[0]['next_run_in'];

        $this->assertGreaterThan(0, $nextRunIn);

        usleep(50_000);
        $scheduler->tick();

        $this->assertSame(2, $executionCount);
    }

    #[Test]
    public function tick_multiple_tasks_executes_independently(): void
    {
        $config = $this->createConfig(checkInterval: 5);
        $scheduler = new Scheduler($config);

        $task1Executed = false;
        $task2Executed = false;

        $task1 = $this->createScheduledTask(
            callback: function () use (&$task1Executed): void {
                $task1Executed = true;
            },
            intervalMs: 10,
            startDelayMs: null,
        );

        $task2 = $this->createScheduledTask(
            callback: function () use (&$task2Executed): void {
                $task2Executed = true;
            },
            intervalMs: 10,
            startDelayMs: 100,
        );

        $scheduler->addTask($task1);
        $scheduler->addTask($task2);

        usleep(15_000);
        $scheduler->tick();

        $this->assertTrue($task1Executed, 'Task 1 should execute immediately');
        $this->assertFalse($task2Executed, 'Task 2 should not execute yet');

        usleep(100_000);
        $scheduler->tick();

        $this->assertTrue($task2Executed, 'Task 2 should execute after delay');
    }

    #[Test]
    public function tick_executes_repeatedly_on_interval(): void
    {
        $config = $this->createConfig(checkInterval: 5);
        $scheduler = new Scheduler($config);

        $executionCount = 0;
        $task = $this->createScheduledTask(
            callback: function () use (&$executionCount): void {
                ++$executionCount;
            },
            intervalMs: 20,
        );

        $scheduler->addTask($task);

        for ($i = 0; $i < 3; ++$i) {
            usleep(30_000);
            $scheduler->tick();
        }

        $this->assertSame(3, $executionCount);
    }

    #[Test]
    public function tick_handles_exception_in_callback(): void
    {
        $config = $this->createConfig(checkInterval: 5);
        $scheduler = new Scheduler($config);

        $task = $this->createScheduledTask(
            callback: function (): never {
                throw new RuntimeException('Test exception');
            },
            intervalMs: 10,
        );

        $scheduler->addTask($task);

        usleep(15_000);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Test exception');

        $scheduler->tick();
    }

    #[Test]
    public function get_stats_empty_tasks_returns_empty_array(): void
    {
        $config = $this->createConfig();
        $scheduler = new Scheduler($config);

        $stats = $scheduler->getStats();

        $this->assertSame([], $stats);
    }

    #[Test]
    public function get_stats_single_task_returns_correct_data(): void
    {
        $config = $this->createConfig();
        $scheduler = new Scheduler($config);

        $task = $this->createScheduledTask(
            callback: function (): void {},
            intervalMs: 1000,
        );

        $scheduler->addTask($task);

        $stats = $scheduler->getStats();

        $this->assertCount(1, $stats);
        $this->assertArrayHasKey(0, $stats);
        $this->assertArrayHasKey('next_run_in', $stats[0]);
        $this->assertArrayHasKey('last_run_ago', $stats[0]);
    }

    #[Test]
    public function get_stats_multiple_tasks_correct_indices(): void
    {
        $config = $this->createConfig();
        $scheduler = new Scheduler($config);

        $task1 = $this->createScheduledTask(
            callback: function (): void {},
            intervalMs: 100,
        );

        $task2 = $this->createScheduledTask(
            callback: function (): void {},
            intervalMs: 200,
        );

        $task3 = $this->createScheduledTask(
            callback: function (): void {},
            intervalMs: 300,
        );

        $scheduler->addTask($task1);
        $scheduler->addTask($task2);
        $scheduler->addTask($task3);

        $stats = $scheduler->getStats();

        $this->assertCount(3, $stats);
        $this->assertArrayHasKey(0, $stats);
        $this->assertArrayHasKey(1, $stats);
        $this->assertArrayHasKey(2, $stats);
    }

    #[Test]
    public function get_stats_next_run_in_correct_format(): void
    {
        $config = $this->createConfig();
        $scheduler = new Scheduler($config);

        $task = $this->createScheduledTask(
            callback: function (): void {},
            intervalMs: 1000,
        );

        $scheduler->addTask($task);

        $stats = $scheduler->getStats();

        $this->assertIsInt($stats[0]['next_run_in']);
        $this->assertGreaterThanOrEqual(0, $stats[0]['next_run_in']);
    }

    #[Test]
    public function get_stats_last_run_ago_null_for_new_task(): void
    {
        $config = $this->createConfig();
        $scheduler = new Scheduler($config);

        $task = $this->createScheduledTask(
            callback: function (): void {},
            intervalMs: 1000,
        );

        $scheduler->addTask($task);

        $stats = $scheduler->getStats();

        $this->assertNull($stats[0]['last_run_ago']);
    }

    #[Test]
    public function get_stats_last_run_ago_correct_after_execution(): void
    {
        $config = $this->createConfig(checkInterval: 5);
        $scheduler = new Scheduler($config);

        $task = $this->createScheduledTask(
            callback: function (): void {},
            intervalMs: 10,
        );

        $scheduler->addTask($task);

        usleep(15_000);
        $scheduler->tick();

        $stats = $scheduler->getStats();

        $this->assertNotNull($stats[0]['last_run_ago']);
        $this->assertIsInt($stats[0]['last_run_ago']);
        $this->assertGreaterThanOrEqual(0, $stats[0]['last_run_ago']);
    }
}
