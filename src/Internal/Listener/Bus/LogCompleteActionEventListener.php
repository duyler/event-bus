<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\Log;
use Duyler\EventBus\Collection\CompleteActionCollection;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;

class LogCompleteActionEventListener
{
    public function __construct(
        private Log $log,
        private CompleteActionCollection $completeActionCollection,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        $completeAction = $this->completeActionCollection->get($event->task->action->id);
        $this->log->pushCompleteAction($completeAction);
    }
}
