<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\State;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Storage\CompleteActorStorage;

class LogCompleteActorEventListener
{
    public function __construct(
        private readonly State $state,
        private readonly CompleteActorStorage $completeActorStorage,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        if ($this->completeActorStorage->isExists($event->task->actor->getId())) {
            $completeActor = $this->completeActorStorage->get($event->task->actor->getId());
            $this->state->pushCompleteActor($completeActor);
        }
    }
}
