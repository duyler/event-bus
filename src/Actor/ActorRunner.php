<?php

declare(strict_types=1);

namespace Duyler\EventBus\Actor;

use Closure;
use Duyler\EventBus\Contract\ActorRunnerInterface;
use Override;

class ActorRunner implements ActorRunnerInterface
{
    public function __construct(
        private readonly Closure $runner,
        private readonly ?object $argument,
    ) {}

    #[Override]
    public function getCallback(): Closure
    {
        return $this->runner;
    }

    #[Override]
    public function getArgument(): ?object
    {
        return $this->argument;
    }
}
