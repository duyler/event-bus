<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service\Trait;

use Duyler\ActionBus\Dto\Trigger;
use Duyler\ActionBus\Service\TriggerService;

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
