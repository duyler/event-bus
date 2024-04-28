<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Test\Unit\Bus;

use Duyler\ActionBus\Bus\TaskQueue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TaskQueueTest extends TestCase
{
    private TaskQueue $taskQueue;

    #[Test]
    public function dequeue_on_empty_queue(): void
    {
        $this->expectException(RuntimeException::class);
        $this->taskQueue->dequeue();
    }

    protected function setUp(): void
    {
        $this->taskQueue = new TaskQueue();
    }
}
