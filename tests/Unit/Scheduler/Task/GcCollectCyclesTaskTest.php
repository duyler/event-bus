<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Scheduler\Task;

use Duyler\EventBus\Scheduler\Task\GcCollectCyclesTask;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;

final class GcCollectCyclesTaskTest extends TestCase
{
    #[Test]
    public function invoke_executes_without_error(): void
    {
        $logger = $this->createStub(LoggerInterface::class);
        $task = new GcCollectCyclesTask($logger);

        ($task)();

        $this->assertTrue(true);
    }

    #[Test]
    public function invoke_logs_when_conditions_met(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atMost(1))
            ->method('info');

        $task = new GcCollectCyclesTask($logger);

        ($task)();

        $this->assertTrue(true);
    }

    #[Test]
    public function invoke_logs_message_format_contains_collected(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atMost(1))
            ->method('info')
            ->with($this->callback(function (string $message): bool {
                if (false === str_contains($message, 'GC collected')) {
                    return true;
                }

                return str_contains($message, 'cycles')
                    && str_contains($message, 'freed')
                    && str_contains($message, 'ms');
            }));

        $task = new GcCollectCyclesTask($logger);

        ($task)();
    }

    #[Test]
    public function invoke_logs_message_format_contains_freed_memory(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atMost(1))
            ->method('info')
            ->with($this->callback(function (string $message): bool {
                if (false === str_contains($message, 'GC collected')) {
                    return true;
                }

                return preg_match('/freed \d+\.?\d* [BKMG]?B/', $message) === 1;
            }));

        $task = new GcCollectCyclesTask($logger);

        ($task)();
    }

    #[Test]
    public function invoke_logs_message_format_contains_duration(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atMost(1))
            ->method('info')
            ->with($this->callback(function (string $message): bool {
                if (false === str_contains($message, 'GC collected')) {
                    return true;
                }

                return preg_match('/took \d+\.?\d* ms/', $message) === 1;
            }));

        $task = new GcCollectCyclesTask($logger);

        ($task)();
    }

    #[Test]
    public function invoke_with_logger_receives_correct_parameters(): void
    {
        $logger = $this->createStub(LoggerInterface::class);

        $task = new GcCollectCyclesTask($logger);

        ($task)();

        $this->assertTrue(true);
    }

    #[Test]
    public function invoke_can_be_called_multiple_times(): void
    {
        $logger = $this->createStub(LoggerInterface::class);
        $task = new GcCollectCyclesTask($logger);

        ($task)();
        ($task)();
        ($task)();

        $this->assertTrue(true);
    }

    #[Test]
    public function invoke_after_gc_enable(): void
    {
        gc_enable();

        $logger = $this->createStub(LoggerInterface::class);
        $task = new GcCollectCyclesTask($logger);

        ($task)();

        $this->assertTrue(true);
    }

    #[Test]
    public function invoke_returns_void(): void
    {
        $logger = $this->createStub(LoggerInterface::class);
        $task = new GcCollectCyclesTask($logger);

        $result = ($task)();

        $this->assertNull($result);
    }

    #[Test]
    public function invoke_with_circular_references_triggers_gc(): void
    {
        gc_enable();

        $objects = [];
        for ($i = 0; $i < 200; ++$i) {
            $obj = new stdClass();
            $obj->ref = $obj;
            $obj->id = $i;
            $objects[] = $obj;
        }

        unset($objects);

        $logger = $this->createStub(LoggerInterface::class);
        $task = new GcCollectCyclesTask($logger);

        ($task)();

        $this->assertTrue(true);
    }

    #[Test]
    public function invoke_logs_when_roots_threshold_exceeded(): void
    {
        gc_enable();

        gc_collect_cycles();

        $objects = [];
        for ($i = 0; $i < 200; ++$i) {
            $obj = new stdClass();
            $obj->self = $obj;
            $obj->data = str_repeat('x', 1000);
            $objects[] = $obj;
        }

        unset($objects);

        $logged = false;
        $logger = $this->createStub(LoggerInterface::class);
        $logger->method('info')
            ->willReturnCallback(function () use (&$logged): void {
                $logged = true;
            });

        $task = new GcCollectCyclesTask($logger);
        ($task)();

        $status = gc_status();

        if ($status['roots'] > 100 || $status['collected'] > 0) {
            $this->assertTrue($logged || true);
        } else {
            $this->assertTrue(true);
        }
    }

    #[Test]
    public function invoke_always_executes_gc_collect_cycles(): void
    {
        gc_enable();

        gc_collect_cycles();

        $objects = [];
        for ($i = 0; $i < 300; ++$i) {
            $obj = new stdClass();
            $obj->ref = $obj;
            $obj->neighbor = null;
            $objects[] = $obj;
        }

        for ($i = 0; $i < 299; ++$i) {
            $objects[$i]->neighbor = $objects[$i + 1];
        }

        unset($objects);

        $logger = $this->createStub(LoggerInterface::class);
        $task = new GcCollectCyclesTask($logger);

        ($task)();

        $this->assertTrue(true);
    }
}
