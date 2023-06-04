<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Dto\Result;

/**
 * @property \Duyler\EventBus\Service\ResultService $resultService
 */
trait ResultService
{
    public function getResult(string $actionId): Result
    {
        return $this->resultService->getResult($actionId);
    }

    public function resultIsExists(string $actionId): bool
    {
        return $this->resultService->resultIsExists($actionId);
    }
}
