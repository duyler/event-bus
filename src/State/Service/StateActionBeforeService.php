<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Build\Action as ExternalAction;
use Duyler\EventBus\Bus\Action as InternalAction;
use Duyler\EventBus\Bus\ActionContainer;

class StateActionBeforeService
{
    public function __construct(
        private readonly ActionContainer $container,
        private readonly InternalAction $action,
        private readonly ?object $argument,
    ) {}

    public function getContainer(): ActionContainer
    {
        return $this->container;
    }

    public function getAction(): ExternalAction
    {
        return ExternalAction::fromInternal($this->action);
    }

    public function getArgument(): ?object
    {
        return $this->argument;
    }
}
