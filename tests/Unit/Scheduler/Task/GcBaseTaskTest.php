<?php

declare(strict_types=1);

namespace Duyler\EventBus\Test\Unit\Scheduler\Task;

use Duyler\EventBus\Scheduler\Task\GcBaseTask;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConcreteGcTaskForTesting extends GcBaseTask
{
    public function formatBytesPublic(int $bytes): string
    {
        return $this->formatBytes($bytes);
    }
}

final class GcBaseTaskTest extends TestCase
{
    private ConcreteGcTaskForTesting $task;

    protected function setUp(): void
    {
        $this->task = new ConcreteGcTaskForTesting();
    }

    #[Test]
    public function format_bytes_zero_returns_zero_b(): void
    {
        $this->assertSame('0 B', $this->task->formatBytesPublic(0));
    }

    #[Test]
    public function format_bytes_small_value_returns_bytes(): void
    {
        $this->assertSame('500 B', $this->task->formatBytesPublic(500));
    }

    #[Test]
    public function format_bytes_kb_value_returns_kb(): void
    {
        $this->assertSame('1 KB', $this->task->formatBytesPublic(1024));
    }

    #[Test]
    public function format_bytes_mb_value_returns_mb(): void
    {
        $this->assertSame('1 MB', $this->task->formatBytesPublic(1048576));
    }

    #[Test]
    public function format_bytes_gb_value_returns_gb(): void
    {
        $this->assertSame('1 GB', $this->task->formatBytesPublic(1073741824));
    }

    #[Test]
    public function format_bytes_fractoral_kb(): void
    {
        $this->assertSame('1.5 KB', $this->task->formatBytesPublic(1536));
    }

    #[Test]
    public function format_bytes_fractoral_mb(): void
    {
        $result = $this->task->formatBytesPublic(1572864);
        $this->assertSame('1.5 MB', $result);
    }

    #[Test]
    public function format_bytes_fractoral_gb(): void
    {
        $result = $this->task->formatBytesPublic(1610612736);
        $this->assertSame('1.5 GB', $result);
    }

    #[Test]
    public function format_bytes_large_value_caps_at_gb(): void
    {
        $twoGb = 2147483648;
        $result = $this->task->formatBytesPublic($twoGb);
        $this->assertSame('2 GB', $result);
    }

    #[Test]
    public function format_bytes_very_large_value_stays_in_gb(): void
    {
        $fourGb = 4294967296;
        $result = $this->task->formatBytesPublic($fourGb);
        $this->assertSame('4 GB', $result);
    }

    #[Test]
    public function format_bytes_one_byte(): void
    {
        $this->assertSame('1 B', $this->task->formatBytesPublic(1));
    }

    #[Test]
    public function format_bytes_just_below_kb(): void
    {
        $this->assertSame('1023 B', $this->task->formatBytesPublic(1023));
    }

    #[Test]
    public function format_bytes_just_above_kb(): void
    {
        $result = $this->task->formatBytesPublic(1025);
        $this->assertStringContainsString('KB', $result);
    }

    #[Test]
    public function format_bytes_rounds_to_two_decimals(): void
    {
        $result = $this->task->formatBytesPublic(1234567);
        $this->assertSame('1.18 MB', $result);
    }
}
