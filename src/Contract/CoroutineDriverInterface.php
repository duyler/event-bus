<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

interface CoroutineDriverInterface
{
    public function process(callable $coroutine, mixed $value): void;
}
