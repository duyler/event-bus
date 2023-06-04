<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\BusService;

/**
 * @property BusService $busService
 */
trait LogService
{
    public function getFirstAction(): string
    {
        return $this->busService->getFirstAction();
    }

    public function getLastAction(): string
    {
        return $this->busService->getLastAction();
    }
}
