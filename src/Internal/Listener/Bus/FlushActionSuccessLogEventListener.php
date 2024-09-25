<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\Log;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Storage\CompleteActionStorage;

class FlushActionSuccessLogEventListener
{
    public function __construct(
        private Log $log,
        private CompleteActionStorage $completeActionStorage,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        if ($event->task->action->flush) {
            if (ResultStatus::Success === $this->completeActionStorage->getResult($event->task->action->id)->status) {
                $this->log->flushSuccessLog();
            }
        }
    }
}
