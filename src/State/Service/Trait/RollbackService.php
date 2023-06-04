<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\BusService;

/**
 * @property BusService $busService
 */
trait RollbackService
{
    public function rollbackWithoutException(int $step = 0): void
    {
        $this->busService->rollbackWithoutException($step);
    }
}
