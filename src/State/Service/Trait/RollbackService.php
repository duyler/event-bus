<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

/**
 * @property \Duyler\EventBus\Service\RollbackService $rollbackService
 */
trait RollbackService
{
    public function rollbackWithoutException(int $step = 0): void
    {
        $this->rollbackService->rollbackWithoutException($step);
    }
}
