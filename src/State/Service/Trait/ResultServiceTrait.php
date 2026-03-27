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
    public function getResult(string|UnitEnum $actorId, string $scope = 'common'): Result
    {
        return $this->resultService->getResult(IdFormatter::toString($actorId), $scope);
    }

    public function resultIsExists(string|UnitEnum $actorId, string $scope = 'common'): bool
    {
        return $this->resultService->resultIsExists(IdFormatter::toString($actorId), $scope);
    }
}
