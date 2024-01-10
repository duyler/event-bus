<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Exception\CircularCallActionException;
use Duyler\EventBus\Exception\ConsecutiveRepeatedActionException;
use Duyler\EventBus\Service\SubscriptionService;
use Duyler\EventBus\Service\ValidateService;

readonly class EventDispatcher
{
    public function __construct(
        private Log $log,
        private SubscriptionService $subscriptionService,
        private Bus $bus,
        private ValidateService $validateService,
    ) {}

    /**
     * @throws ConsecutiveRepeatedActionException
     * @throws CircularCallActionException
     */
    public function dispatch(Event $event): void
    {
        $this->log->pushActionLog($event->action->id);
        $this->validateService->validateEvent($event);
        if ($event->action->silent === false) {
            $this->subscriptionService->resolveSubscriptions($event->action->id, $event->result->status);
        }
        $this->bus->resolveHeldTasks();
    }
}
