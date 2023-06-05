<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

/**
 * @property \Duyler\EventBus\Service\LogService $logService
 */
trait LogService
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
