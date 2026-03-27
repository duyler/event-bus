<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Service\LogService;

/**
 * @property LogService $logService
 */
trait LogServiceTrait
{
    public function getFirstActor(): ?string
    {
        return $this->logService->getFirstActor();
    }

    public function getLastActor(): ?string
    {
        return $this->logService->getLastActor();
    }

    public function flushSuccessLog(): void
    {
        $this->logService->flushSuccessLog();
    }
}
