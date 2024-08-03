<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Contract;

use Duyler\ActionBus\Dto\Rollback as RollbackDto;

interface RollbackActionInterface
{
    public function run(RollbackDto $rollback): void;
}
