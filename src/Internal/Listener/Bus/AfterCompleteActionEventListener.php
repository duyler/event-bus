<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\Bus;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Storage\ActionContainerStorage;
use Duyler\EventBus\Storage\CompleteActionStorage;

final readonly class AfterCompleteActionEventListener
{
    public function __construct(
        private ActionContainerStorage $containerStorage,
        private CompleteActionStorage $completeActionStorage,
        private Bus $bus,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        if (false === $this->completeActionStorage->isExists($event->task->action->getId(), $event->task->getCorrelationId())) {
            return;
        }

        $completeAction = $this->completeActionStorage->get($event->task->action->getId(), $event->task->getCorrelationId());

        $this->bus->afterCompleteAction($completeAction);

        if ($this->containerStorage->isExists($event->task->action->getId(), $event->task->getCorrelationId())) {
            $actionContainer = $this->containerStorage->get($event->task->action->getId(), $event->task->getCorrelationId());
            $actionContainer->finalize();
        }
    }
}
