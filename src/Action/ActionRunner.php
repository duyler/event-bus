<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Override;
use Closure;
use Duyler\EventBus\Contract\ActionRunnerInterface;

class ActionRunner implements ActionRunnerInterface
{
    public function __construct(
        private Closure $runner,
        private ?object $argument,
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
