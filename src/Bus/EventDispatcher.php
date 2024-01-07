<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Exception\CircularCallActionException;
use Duyler\EventBus\Exception\ConsecutiveRepeatedActionException;
use Duyler\EventBus\Service\SubscriptionService;

readonly class EventDispatcher
{
    public function __construct(
        private Log $log,
        private SubscriptionService $subscriptionService,
        private Bus $bus,
    ) {}

    /**
     * @throws ConsecutiveRepeatedActionException
     * @throws CircularCallActionException
     */
    public function dispatch(Event $event): void
    {
        $this->log->pushActionLog($event->action->id);
        $this->validateEvent($event);
        if ($event->action->silent === false) {
            $this->subscriptionService->resolveSubscriptions($event->action->id, $event->result->status);
        }
        $this->bus->resolveHeldTasks();
    }

    /**
     * @throws ConsecutiveRepeatedActionException
     * @throws CircularCallActionException
     */
    public function validateEvent(Event $event): void
    {
        $actionId = $event->action->id . '.' . $event->result->status->value;

        if (in_array($actionId, $this->log->getMainEventLog()) && false === $event->action->repeatable) {
            $this->log->pushRepeatedEventLog($actionId);
        } else {
            $this->log->pushMainEventLog($actionId);
        }

        $mainEventLog = $this->log->getMainEventLog();
        $repeatedEventLog = $this->log->getRepeatedEventLog();

        if (end($repeatedEventLog) === $actionId && false === $event->action->repeatable) {
            throw new ConsecutiveRepeatedActionException($event->action->id, $event->result->status->value);
        }

        if (count($mainEventLog) === count($repeatedEventLog)) {
            throw new CircularCallActionException($event->action->id, end($mainEventLog));
        }
    }
}
