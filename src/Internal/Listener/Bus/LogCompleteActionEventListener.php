<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\Bus;

use Duyler\ActionBus\Bus\Log;
use Duyler\ActionBus\Collection\CompleteActionCollection;
use Duyler\ActionBus\Internal\Event\TaskAfterRunEvent;

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
