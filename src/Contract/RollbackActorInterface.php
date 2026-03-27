<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

use Duyler\EventBus\Dto\Rollback as RollbackDto;

interface RollbackActorInterface
{
    public function run(RollbackDto $rollback): void;
}
