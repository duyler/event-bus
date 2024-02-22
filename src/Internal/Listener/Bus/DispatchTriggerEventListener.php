<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Internal\Event\TriggerPushedEvent;
use Duyler\EventBus\Service\TriggerService;

class DispatchTriggerEventListener
{
    public function __construct(private TriggerService $triggerService) {}

    public function __invoke(TriggerPushedEvent $event): void
    {
        $this->triggerService->dispatch($event->trigger);
    }
}
