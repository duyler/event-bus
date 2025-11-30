<?php

declare(strict_types=1);

namespace Duyler\EventBus\Scheduler\Task;

use Psr\Log\LoggerInterface;

final class GcMemCachesTask extends GcBaseTask
{
    private const int MIN_ACTUAL_FREED = 50000;

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(): void
    {
        $startTime = hrtime(true);
        $startMemory = memory_get_usage(true);

        $freed = gc_mem_caches();

        $duration = (float) (hrtime(true) - $startTime) / 1_000_000.0;
        $actualFreed = $startMemory - memory_get_usage(true);

        if ($actualFreed > self::MIN_ACTUAL_FREED) {
            $this->logger->info(sprintf(
                "Mem caches freed %s (reported: %s), took %.2f ms",
                $this->formatBytes($actualFreed),
                $this->formatBytes($freed),
                $duration,
            ));
        }
    }
}
