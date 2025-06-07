<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Build\Action as ExternalAction;
use Duyler\EventBus\Bus\Action as InternalAction;
use Duyler\EventBus\Bus\ActionContainer;
use Throwable;

class StateActionThrowingService
{
    public function __construct(
        private readonly ActionContainer $container,
        private readonly Throwable $exception,
        private readonly InternalAction $action,
    ) {}

    public function getContainer(): ActionContainer
    {
        return $this->container;
    }

    public function getAction(): ExternalAction
    {
        return ExternalAction::fromInternal($this->action);
    }

    public function getException(): Throwable
    {
        return $this->exception;
    }
}
