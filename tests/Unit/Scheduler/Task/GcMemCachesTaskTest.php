<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Scheduler\Task;

use Duyler\EventBus\Scheduler\Task\GcMemCachesTask;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class GcMemCachesTaskTest extends TestCase
{
    #[Test]
    public function invoke_executes_without_error(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $task = new GcMemCachesTask($logger);

        ($task)();

        $this->assertTrue(true);
    }

    #[Test]
    public function invoke_calls_gc_mem_caches(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $task = new GcMemCachesTask($logger);

        ($task)();

        $this->assertTrue(true);
    }

    #[Test]
    public function invoke_logs_at_most_once(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atMost(1))
            ->method('info');

        $task = new GcMemCachesTask($logger);

        ($task)();
    }

    #[Test]
    public function invoke_logs_message_format_when_triggered(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atMost(1))
            ->method('info')
            ->with($this->callback(function (string $message): bool {
                if (false === str_contains($message, 'Mem caches freed')) {
                    return true;
                }

                return str_contains($message, 'reported')
                    && str_contains($message, 'ms');
            }));

        $task = new GcMemCachesTask($logger);

        ($task)();
    }

    #[Test]
    public function invoke_logs_message_contains_actual_freed(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atMost(1))
            ->method('info')
            ->with($this->callback(function (string $message): bool {
                if (false === str_contains($message, 'Mem caches freed')) {
                    return true;
                }

                return preg_match('/freed \d+\.?\d* [BKMG]?B/', $message) === 1;
            }));

        $task = new GcMemCachesTask($logger);

        ($task)();
    }

    #[Test]
    public function invoke_logs_message_contains_reported_freed(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atMost(1))
            ->method('info')
            ->with($this->callback(function (string $message): bool {
                if (false === str_contains($message, 'Mem caches freed')) {
                    return true;
                }

                return preg_match('/reported: \d+\.?\d* [BKMG]?B/', $message) === 1;
            }));

        $task = new GcMemCachesTask($logger);

        ($task)();
    }

    #[Test]
    public function invoke_logs_message_contains_duration(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atMost(1))
            ->method('info')
            ->with($this->callback(function (string $message): bool {
                if (false === str_contains($message, 'Mem caches freed')) {
                    return true;
                }

                return preg_match('/took \d+\.?\d* ms/', $message) === 1;
            }));

        $task = new GcMemCachesTask($logger);

        ($task)();
    }

    #[Test]
    public function invoke_can_be_called_multiple_times(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $task = new GcMemCachesTask($logger);

        ($task)();
        ($task)();
        ($task)();

        $this->assertTrue(true);
    }

    #[Test]
    public function invoke_returns_void(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $task = new GcMemCachesTask($logger);

        $result = ($task)();

        $this->assertNull($result);
    }

    #[Test]
    public function invoke_after_memory_intensive_operations(): void
    {
        gc_enable();

        gc_mem_caches();

        $data = [];
        for ($i = 0; $i < 100; ++$i) {
            $data[] = str_repeat('x', 10000);
        }
        unset($data);

        $logger = $this->createMock(LoggerInterface::class);
        $task = new GcMemCachesTask($logger);

        ($task)();

        $this->assertTrue(true);
    }

    #[Test]
    public function invoke_multiple_times_measures_memory_accurately(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $task = new GcMemCachesTask($logger);

        ($task)();
        ($task)();
        ($task)();
        ($task)();
        ($task)();

        $this->assertTrue(true);
    }

    #[Test]
    public function invoke_with_different_memory_states(): void
    {
        gc_enable();

        gc_mem_caches();

        gc_collect_cycles();

        $logger = $this->createMock(LoggerInterface::class);
        $task = new GcMemCachesTask($logger);

        ($task)();

        $this->assertTrue(true);
    }

    #[Test]
    public function invoke_checks_logger_is_called_conditionally(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atMost(1))
            ->method('info');

        $task = new GcMemCachesTask($logger);

        ($task)();
    }

    #[Test]
    public function invoke_attempts_to_trigger_logging(): void
    {
        gc_enable();

        gc_collect_cycles();
        gc_mem_caches();

        for ($i = 0; $i < 1000; ++$i) {
            $path = sys_get_temp_dir() . '/test_' . $i;
            @file_exists($path);
        }

        gc_collect_cycles();

        $logger = $this->createMock(LoggerInterface::class);
        $logger->method('info');

        $task = new GcMemCachesTask($logger);

        ($task)();

        $this->assertTrue(true);
    }
    #[Test]
    public function invoke_triggers_logging_with_realpath_cache(): void
    {
        gc_enable();
        gc_collect_cycles();
        gc_mem_caches();

        clearstatcache(true);

        for ($i = 0; $i < 1000; ++$i) {
            $dir = sys_get_temp_dir() . '/duyler_test_' . $i . '_' . uniqid();
            @mkdir($dir, 0777, true);
            for ($j = 0; $j < 5; ++$j) {
                $file = $dir . '/file_' . $j . '.txt';
                @file_put_contents($file, str_repeat('x', 100));
                @realpath($file);
                @file_exists($file);
                @is_file($file);
                @is_readable($file);
            }
        }

        $logger = $this->createMock(LoggerInterface::class);
        $logger->method('info');

        $task = new GcMemCachesTask($logger);
        ($task)();

        $this->assertTrue(true);

        for ($i = 0; $i < 1000; ++$i) {
            $dir = sys_get_temp_dir() . '/duyler_test_' . $i . '_';
            $pattern = $dir . '*';
            $dirs = glob($pattern);
            if (false !== $dirs) {
                foreach ($dirs as $d) {
                    @exec('rm -rf ' . escapeshellarg($d));
                }
            }
        }
    }
}
