<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\State;
use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;

class LogCompleteActionEventListener
{
    public function __construct(
        private readonly State $state,
        private readonly CompleteActionStorage $completeActionStorage,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        if ($this->completeActionStorage->isExists($event->task->action->id)) {
            $completeAction = $this->completeActionStorage->get($event->task->action->id);
            $this->state->pushCompleteAction($completeAction);
        }
    }
}
