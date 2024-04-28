<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service\Trait;

use Duyler\ActionBus\Service\LogService;

/**
 * @property LogService $logService
 */
trait LogServiceTrait
{
    public function getFirstAction(): ?string
    {
        return $this->logService->getFirstAction();
    }

    public function getLastAction(): ?string
    {
        return $this->logService->getLastAction();
    }
}
