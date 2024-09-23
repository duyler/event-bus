<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Bus\Rollback;

readonly class RollbackService
{
    public function __construct(
        private Rollback $rollback,
    ) {}

    public function rollbackWithoutException(): void
    {
        $this->rollback->run();
    }
}
