<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

use Duyler\EventBus\Dto\Rollback as RollbackDto;

interface RollbackActionInterface
{
    public function run(RollbackDto $rollback): void;
}
