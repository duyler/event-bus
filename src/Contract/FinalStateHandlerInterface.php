<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

interface FinalStateHandlerInterface
{
    public function handle(): void;
}
