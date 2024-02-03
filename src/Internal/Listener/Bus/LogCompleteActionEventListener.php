<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\Log;
use Duyler\EventBus\Internal\Event\ActionIsCompleteEvent;

class LogCompleteActionEventListener
{
    public function __construct(private Log $log) {}

    public function __invoke(ActionIsCompleteEvent $event): void
    {
        $this->log->pushActionLog($event->completeAction->action);
    }
}
