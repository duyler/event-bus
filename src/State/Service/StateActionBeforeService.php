<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Action\ActionContainer;

class StateActionBeforeService
{
    public function __construct(
        private readonly ActionContainer $container,
        private readonly string $actionId,
    ) {}

    public function getContainer(): ActionContainer
    {
        return $this->container;
    }

    public function getActionId(): string
    {
        return $this->actionId;
    }
}
