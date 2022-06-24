<?php

declare(strict_types=1);

namespace Konveyer\EventBus\Contract;

interface ValidateCacheHandlerInterface
{
    public function readHash(): string;
    public function writeHash(string $hash): void;
}
