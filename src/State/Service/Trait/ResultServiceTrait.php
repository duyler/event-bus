<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Control;
use Duyler\EventBus\Dto\Result;

/**
 * @property Control $control
 */
trait ResultServiceTrait
{
    public function getResult(string $actionId): Result
    {
        return $this->control->getResult($actionId);
    }

    public function resultIsExists(string $actionId): bool
    {
        return $this->control->resultIsExists($actionId);
    }
}
