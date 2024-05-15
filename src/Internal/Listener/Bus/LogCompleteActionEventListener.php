<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\Bus;

use Duyler\ActionBus\Bus\Log;
use Duyler\ActionBus\Storage\CompleteActionStorage;
use Duyler\ActionBus\Internal\Event\TaskAfterRunEvent;

class LogCompleteActionEventListener
{
    public function __construct(
        private Log $log,
        private CompleteActionStorage $completeActionStorage,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        $completeAction = $this->completeActionStorage->get($event->task->action->id);
        $this->log->pushCompleteAction($completeAction);
    }
}
