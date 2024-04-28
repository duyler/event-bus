<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service;

use Duyler\ActionBus\Bus\ActionContainer;
use Duyler\ActionBus\Dto\Action;
use Throwable;

class StateActionThrowingService
{
    public function __construct(
        private readonly ActionContainer $container,
        private readonly Throwable $exception,
        private readonly Action $action,
    ) {}

    public function getContainer(): ActionContainer
    {
        return $this->container;
    }

    public function getAction(): Action
    {
        return $this->action;
    }

    public function getException(): Throwable
    {
        return $this->exception;
    }
}
