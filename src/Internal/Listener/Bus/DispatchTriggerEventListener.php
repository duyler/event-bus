<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\Bus;

use Duyler\ActionBus\Bus\Log;
use Duyler\ActionBus\Internal\Event\TriggerPushedEvent;
use Duyler\ActionBus\Service\TriggerService;

class DispatchTriggerEventListener
{
    public function __construct(
        private TriggerService $triggerService,
        private Log $log,
    ) {}

    public function __invoke(TriggerPushedEvent $event): void
    {
        $this->log->pushTriggerLog($event->trigger->id);
        $this->triggerService->dispatch($event->trigger);
    }
}
