<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Internal\Event\EventRemovedEvent;
use Duyler\EventBus\Service\ActorService;
use Duyler\EventBus\Storage\ActorStorage;

class ResolveActorsAfterEventDeletedEventListener
{
    public function __construct(
        private readonly ActorService $actorService,
        private readonly ActorStorage $actorStorage,
    ) {}

    public function __invoke(EventRemovedEvent $event): void
    {
        $actors = $this->actorStorage->getBySubscriptionEvent($event->event->id);

        foreach ($actors as $actor) {
            $this->actorService->removeActor($actor->getId());
        }
    }
}
