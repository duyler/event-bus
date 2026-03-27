<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

use Closure;

interface ActorRunnerInterface
{
    public function getCallback(): Closure;

    public function getArgument(): ?object;
}
