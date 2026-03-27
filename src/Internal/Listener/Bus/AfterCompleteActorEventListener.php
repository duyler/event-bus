<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\Bus;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Storage\ActorContainerStorage;
use Duyler\EventBus\Storage\CompleteActorStorage;

final readonly class AfterCompleteActorEventListener
{
    public function __construct(
        private ActorContainerStorage $containerStorage,
        private CompleteActorStorage $completeActorStorage,
        private Bus $bus,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        if (false === $this->completeActorStorage->isExists($event->task->actor->getId(), $event->task->getScope())) {
            return;
        }

        $completeActor = $this->completeActorStorage->get($event->task->actor->getId(), $event->task->getScope());

        $this->bus->afterCompleteActor($completeActor);

        if ($this->containerStorage->isExists($event->task->actor->getId(), $event->task->getScope())) {
            $actorContainer = $this->containerStorage->get($event->task->actor->getId(), $event->task->getScope());
            $actorContainer->finalize();
        }
    }
}
