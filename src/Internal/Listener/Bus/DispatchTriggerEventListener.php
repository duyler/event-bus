<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\Log;
use Duyler\EventBus\Internal\Event\TriggerPushedEvent;
use Duyler\EventBus\Service\TriggerService;

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
