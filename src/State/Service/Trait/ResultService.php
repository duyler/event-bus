<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\BusService;
use Duyler\EventBus\Dto\Result;

/**
 * @property BusService $busService
 */
trait ResultService
{
    public function getResult(string $actionId): Result
    {
        return $this->busService->getResult($actionId);
    }

    public function resultIsExists(string $actionId): bool
    {
        return $this->busService->resultIsExists($actionId);
    }
}
