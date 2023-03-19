<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

interface RollbackActionInterface
{
    public function run();
}
