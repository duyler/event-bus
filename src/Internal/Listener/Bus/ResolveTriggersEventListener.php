<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Service\TriggerService;

class ResolveTriggersEventListener
{
    public function __construct(
        private readonly TriggerService $triggerService,
        private readonly CompleteActionStorage $completeActionStorage,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        if ($this->completeActionStorage->isExists($event->task->action->id)) {
            $completeAction = $this->completeActionStorage->get($event->task->action->id);
            $this->triggerService->resolveTriggers($completeAction);
        }
    }
}
