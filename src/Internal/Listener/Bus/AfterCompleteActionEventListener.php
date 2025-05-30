<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\Bus;
use Duyler\EventBus\Storage\ActionContainerStorage;
use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;

final readonly class AfterCompleteActionEventListener
{
    public function __construct(
        private ActionContainerStorage $containerStorage,
        private CompleteActionStorage $completeActionStorage,
        private Bus $bus,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        if (false === $this->completeActionStorage->isExists(($event->task->action->id))) {
            return;
        }

        $completeAction = $this->completeActionStorage->get($event->task->action->id);

        $this->bus->afterCompleteAction($completeAction);

        $actionContainer = $this->containerStorage->get($event->task->action->id);
        $actionContainer->finalize();
    }
}
