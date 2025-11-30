<?php

declare(strict_types=1);

namespace Duyler\EventBus\Scheduler\Task;

use Psr\Log\LoggerInterface;

final class GcCollectCyclesTask extends GcBaseTask
{
    private const int MIN_FREED_MEMORY = 100000;

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(): void
    {
        $status = gc_status();

        if ($status['roots'] > 100 || $status['collected'] > 0) {
            $startTime = hrtime(true);
            $startMemory = memory_get_usage(true);

            $collected = gc_collect_cycles();
            $freedMemory = $startMemory - memory_get_usage(true);

            $duration = (float) (hrtime(true) - $startTime) / 1_000_000.0;

            if ($collected > 0 || $freedMemory > self::MIN_FREED_MEMORY) {
                $this->logger->info(sprintf(
                    "GC collected %d cycles, freed %s, took %.2f ms",
                    $collected,
                    $this->formatBytes($freedMemory),
                    $duration,
                ));
            }
        }
    }
}
