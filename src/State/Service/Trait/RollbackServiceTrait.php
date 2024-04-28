<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service\Trait;

use Duyler\ActionBus\Service\RollbackService;

/**
 * @property RollbackService $rollbackService
 */
trait RollbackServiceTrait
{
    public function rollbackWithoutException(int $step = 0): void
    {
        $this->rollbackService->rollbackWithoutException($step);
    }
}
