<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Build\Trigger;
use Duyler\EventBus\Service\TriggerService;

/**
 * @property TriggerService $triggerService
 */
trait TriggerServiceTrait
{
    public function addTrigger(Trigger $trigger): void
    {
        $this->triggerService->addTrigger($trigger);
    }

    public function triggerIsExists(Trigger $trigger): bool
    {
        return $this->triggerService->triggerIsExists($trigger);
    }

    public function removeTrigger(Trigger $trigger): void
    {
        $this->triggerService->remove($trigger);
    }
}
