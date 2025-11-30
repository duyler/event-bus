<?php

declare(strict_types=1);

namespace Duyler\EventBus\Scheduler;

use Duyler\EventBus\BusConfig;

final class Scheduler
{
    /**
     * @var array<array{
     *     callback: callable,
     *     interval: int,
     *     next_run: int,
     *     last_run: int
     * }>
     */
    private array $tasks = [];
    private int $lastCheck;
    private readonly int $checkInterval;

    public function __construct(BusConfig $config)
    {
        $checkIntervalMsFloat = (float) $config->schedulerCheckIntervalMs;
        $checkIntervalFloat = $checkIntervalMsFloat * 1_000_000.0;
        $this->checkInterval = (int) $checkIntervalFloat;
        $this->lastCheck = hrtime(true);
    }

    public function addTask(callable $callback, int $intervalMs, ?int $startDelayMs = null): void
    {
        $nowNs = hrtime(true);
        $nowNsFloat = (float) $nowNs;
        $nowFloat = $nowNsFloat / 1_000_000.0;
        $now = (int) $nowFloat;

        $startTime = $startDelayMs !== null ? $now + $startDelayMs : $now;

        $this->tasks[] = [
            'callback' => $callback,
            'interval' => $intervalMs,
            'next_run' => $startTime,
            'last_run' => 0,
        ];
    }

    public function tick(): void
    {
        $nowNs = hrtime(true);

        if ($nowNs - $this->lastCheck < $this->checkInterval) {
            return;
        }

        $this->lastCheck = $nowNs;

        $nowNsFloat = (float) $nowNs;
        $nowMsFloat = $nowNsFloat / 1_000_000.0;
        $nowMs = (int) $nowMsFloat;

        foreach ($this->tasks as &$task) {
            if ($nowMs >= $task['next_run']) {
                $task['callback']();
                $task['last_run'] = $nowMs;
                $task['next_run'] = $nowMs + $task['interval'];
            }
        }
    }

    public function getStats(): array
    {
        $nowNs = hrtime(true);
        $nowNsFloat = (float) $nowNs;
        $nowMsFloat = $nowNsFloat / 1_000_000.0;
        $nowMs = (int) $nowMsFloat;

        $stats = [];

        foreach ($this->tasks as $i => $task) {
            $nextRunIn = max(0, $task['next_run'] - $nowMs);
            $lastRunAgo = $task['last_run'] !== 0 ? $nowMs - $task['last_run'] : null;

            $stats[$i] = [
                'next_run_in' => $nextRunIn,
                'last_run_ago' => $lastRunAgo,
            ];
        }

        return $stats;
    }
}
