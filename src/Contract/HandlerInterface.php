<?php

declare(strict_types=1);

namespace Jine\EventBus\Contract;

use Jine\EventBus\Dto\Result;

interface HandlerInterface
{
    public function run(): ?Result;
}
