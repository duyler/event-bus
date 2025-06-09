<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\State;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Storage\CompleteActionStorage;

class LogCompleteActionEventListener
{
    public function __construct(
        private readonly State $state,
        private readonly CompleteActionStorage $completeActionStorage,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        if ($this->completeActionStorage->isExists($event->task->action->getId())) {
            $completeAction = $this->completeActionStorage->get($event->task->action->getId());
            $this->state->pushCompleteAction($completeAction);
        }
    }
}
