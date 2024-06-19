<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service;

use Duyler\ActionBus\Build\Action;
use Duyler\ActionBus\Bus\ActionContainer;

class StateActionBeforeService
{
    public function __construct(
        private readonly ActionContainer $container,
        private readonly Action $action,
        private readonly object|null $argument,
    ) {}

    public function getContainer(): ActionContainer
    {
        return $this->container;
    }

    public function getAction(): Action
    {
        return $this->action;
    }

    public function getArgument(): null|object
    {
        return $this->argument;
    }
}
