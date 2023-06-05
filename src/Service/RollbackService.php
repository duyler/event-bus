<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Log;
use Duyler\EventBus\Rollback;

readonly class RollbackService
{
    public function __construct(private Rollback $rollback, private Log $log)
    {
    }

    public function rollbackWithoutException(int $step = 0): void
    {
        $this->rollback->run($step > 0 ? array_slice($this->log->getActionLog(), -1, $step) : []);
    }
}
