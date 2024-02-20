<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

use Duyler\EventBus\Dto\Result;

interface RollbackActionInterface
{
    public function run(Result $result): void;
}
