<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service\Trait;

use Duyler\ActionBus\Dto\Result;
use Duyler\ActionBus\Formatter\ActionIdFormatter;
use Duyler\ActionBus\Service\ResultService;
use UnitEnum;

/**
 * @property ResultService $resultService
 */
trait ResultServiceTrait
{
    public function getResult(string|UnitEnum $actionId): Result
    {
        return $this->resultService->getResult(ActionIdFormatter::toString($actionId));
    }

    public function resultIsExists(string|UnitEnum $actionId): bool
    {
        return $this->resultService->resultIsExists(ActionIdFormatter::toString($actionId));
    }
}
