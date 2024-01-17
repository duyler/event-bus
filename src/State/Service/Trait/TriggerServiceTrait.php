<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Dto\Trigger;
use Duyler\EventBus\Service\TriggerService;

/**
 * @property TriggerService $triggerService
 */
trait TriggerServiceTrait
{
    public function doTrigger(Trigger $trigger): void
    {
        $this->triggerService->dispatch($trigger);
    }
}
