<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Internal\Event\EventRemovedEvent;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Storage\ActionStorage;

class ResolveActionsAfterEventDeletedEventListener
{
    public function __construct(
        private readonly ActionService $actionService,
        private readonly ActionStorage $actionStorage,
    ) {}

    public function __invoke(EventRemovedEvent $event): void
    {
        $actions = $this->actionStorage->getByEvent($event->event->id);

        foreach ($actions as $action) {
            $this->actionService->removeAction($action->getId());
        }
    }
}
