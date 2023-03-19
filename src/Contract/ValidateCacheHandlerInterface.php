<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

interface ValidateCacheHandlerInterface
{
    public function readHash(): string;
    public function writeHash(string $hash): void;
}
