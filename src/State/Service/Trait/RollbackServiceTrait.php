<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Service\RollbackService;

/**
 * @property RollbackService $rollbackService
 */
trait RollbackServiceTrait
{
    public function rollbackWithoutException(): void
    {
        $this->rollbackService->rollbackWithoutException();
    }
}
