<?php

declare(strict_types=1);

namespace Konveyer\EventBus\Contract;

interface RollbackInterface
{
    public function run();
}
