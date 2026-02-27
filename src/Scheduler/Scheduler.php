<?php

declare(strict_types=1);

namespace Duyler\EventBus\Scheduler;

use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Dto\ScheduledTask;

final class Scheduler
{
    /** @var Task[] */
    private array $tasks = [];
    private int $lastCheck;
    private readonly int $checkInterval;

    public function __construct(BusConfig $config)
    {
        $checkIntervalMsFloat = (float) $config->schedulerCheckInterval;
        $checkIntervalFloat = $checkIntervalMsFloat * 1_000_000.0;
        $this->checkInterval = (int) $checkIntervalFloat;
        $this->lastCheck = hrtime(true);
    }

    public function addTask(ScheduledTask $task): void
    {
        $now = $this->nowMs();
        $startTime = null !== $task->getStartDelay() ? $now + $task->getStartDelay() : $now;
        $this->tasks[] = new Task(
            callback: $task->getCallback(),
            interval: $task->getInterval(),
            nextRun: $startTime,
            lastRun: 0,
        );
    }

    public function tick(): void
    {
        $nowNs = hrtime(true);

        if ($nowNs - $this->lastCheck < $this->checkInterval) {
            return;
        }

        $this->lastCheck = $nowNs;
        $nowMs = $this->nowMs();

        foreach ($this->tasks as $i => $task) {
            if ($nowMs >= $task->nextRun) {
                ($task->callback)();
                $this->tasks[$i] = new Task(
                    callback: $task->callback,
                    interval: $task->interval,
                    nextRun: $nowMs + $task->interval,
                    lastRun: $nowMs,
                );
            }
        }
    }

    public function getStats(): array
    {
        $nowMs = $this->nowMs();

        $stats = [];

        foreach ($this->tasks as $i => $task) {
            $nextRunIn = max(0, $task->nextRun - $nowMs);
            $lastRunAgo = 0 !== $task->lastRun ? $nowMs - $task->lastRun : null;

            $stats[$i] = [
                'next_run_in' => $nextRunIn,
                'last_run_ago' => $lastRunAgo,
            ];
        }

        return $stats;
    }

    private function nowMs(): int
    {
        $nowNsFloat = (float) hrtime(true);
        return (int) ($nowNsFloat / 1_000_000.0);
    }
}
