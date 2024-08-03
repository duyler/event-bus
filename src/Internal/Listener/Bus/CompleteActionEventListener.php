<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\Bus;

use Duyler\ActionBus\Bus\Bus;
use Duyler\ActionBus\Bus\CompleteAction;
use Duyler\ActionBus\Storage\ActionContainerStorage;
use Duyler\ActionBus\Storage\CompleteActionStorage;
use Duyler\ActionBus\Internal\Event\TaskAfterRunEvent;

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
