<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Dto\Trigger;

/**
 * @property \Duyler\EventBus\Service\TriggerService $triggerService
 */
trait TriggerService
{
    public function doTrigger(Trigger $trigger): void
    {
        $this->triggerService->dispatch($trigger);
    }
}
