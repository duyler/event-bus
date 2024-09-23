<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\Log;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;

class FlushActionSuccessLogEventListener
{
    public function __construct(
        private Log $log,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        if ($event->task->action->flush) {
            $this->log->flushSuccessLog();
        }
    }
}
