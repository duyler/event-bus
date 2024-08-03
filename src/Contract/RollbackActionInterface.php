<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Contract;

use Duyler\ActionBus\Dto\Result;

interface RollbackActionInterface
{
    public function run(Result|null $result, object|null $argument): void;
}
