<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\Bus;

use Duyler\ActionBus\Internal\Event\EventRemovedEvent;
use Duyler\ActionBus\Service\ActionService;
use Duyler\ActionBus\Storage\ActionStorage;

class ResolveActionsAfterEventDeletedEventListener
{
    public function __construct(
        private ActionService $actionService,
        private ActionStorage $actionStorage,
    ) {}

    public function __invoke(EventRemovedEvent $event): void
    {
        $actions = $this->actionStorage->getByEvent($event->eventId);

        foreach ($actions as $action) {
            $this->actionService->removeAction($action->id);
        }
    }
}
