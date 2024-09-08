<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\Bus;
use Duyler\EventBus\Bus\CompleteAction;
use Duyler\EventBus\Storage\ActionContainerStorage;
use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;

class CompleteActionEventListener
{
    public function __construct(
        private readonly ActionContainerStorage $containerStorage,
        private CompleteActionStorage $completeActionStorage,
        private Bus $bus,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        $completeAction = new CompleteAction(
            action: $event->task->action,
            result: $event->task->getResult(),
        );

        $this->completeActionStorage->save($completeAction);
        $this->bus->finalizeCompleteAction($completeAction);

        $actionContainer = $this->containerStorage->get($event->task->action->id);
        $actionContainer->finalize();
    }
}
