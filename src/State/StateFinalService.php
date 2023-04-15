<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

class StateFinalService extends AbstractStateService implements StateServiceInterface
{
    public function getFirstAction(): string
    {
        return $this->control->getFirstAction();
    }

    public function getLastAction(): string
    {
        return $this->control->getLastAction();
    }
}
