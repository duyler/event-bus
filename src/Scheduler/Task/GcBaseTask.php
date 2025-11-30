<?php

declare(strict_types=1);

namespace Duyler\EventBus\Scheduler\Task;

class GcBaseTask
{
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        if ($bytes === 0) {
            return '0 B';
        }

        $value = (float) $bytes;
        $i = 0;
        $maxIndex = count($units) - 1;

        while ($value >= 1024.0 && $i < $maxIndex) {
            $value /= 1024.0;
            $i++;
        }

        // Двойная проверка границ массива
        $unitIndex = min($i, $maxIndex);
        $unit = $units[$unitIndex];

        $formatted = round($value, 2);
        return ((string) $formatted) . ' ' . $unit;
    }
}
