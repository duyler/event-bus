<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

interface LoopInterface
{
    public function run(): void;

    public function stop(): void;

    public function setResource(ResourceInterface $resource): void;
}
