<?php

declare(strict_types=1);

namespace Jine\EventBus\Contract;

interface RollbackInterface
{
    public function run();
}
