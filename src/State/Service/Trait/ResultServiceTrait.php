<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Service\ResultService;
use UnitEnum;

/**
 * @property ResultService $resultService
 */
trait ResultServiceTrait
{
    public function getResult(string|UnitEnum $actionId): Result
    {
        return $this->resultService->getResult(IdFormatter::toString($actionId));
    }

    public function resultIsExists(string|UnitEnum $actionId): bool
    {
        return $this->resultService->resultIsExists(IdFormatter::toString($actionId));
    }
}
