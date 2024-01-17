<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Service\LogService;

/**
 * @property LogService $logService
 */
trait LogServiceTrait
{
    public function getFirstAction(): string
    {
        return $this->logService->getFirstAction();
    }

    public function getLastAction(): string
    {
        return $this->logService->getLastAction();
    }
}
