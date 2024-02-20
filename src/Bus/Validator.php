<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Exception\CircularCallActionException;
use Duyler\EventBus\Exception\ConsecutiveRepeatedActionException;

class Validator
{
    public function __construct(
        private Log $log,
    ) {}

    /**
     * @throws ConsecutiveRepeatedActionException
     * @throws CircularCallActionException
     */
    public function validateCompleteAction(CompleteAction $completeAction): void
    {
        $actionId = $completeAction->action->id . '.' . $completeAction->result->status->value;

        if (in_array($actionId, $this->log->getMainEventLog()) && false === $completeAction->action->repeatable) {
            $this->log->pushRepeatedEventLog($actionId);
        } else {
            $this->log->pushMainEventLog($actionId);
        }

        $mainEventLog = $this->log->getMainEventLog();
        $repeatedEventLog = $this->log->getRepeatedEventLog();

        if (end($repeatedEventLog) === $actionId && false === $completeAction->action->repeatable) {
            throw new ConsecutiveRepeatedActionException(
                $completeAction->action->id,
                $completeAction->result->status->value
            );
        }

        if (count($mainEventLog) === count($repeatedEventLog)) {
            throw new CircularCallActionException($completeAction->action->id, (string) end($mainEventLog));
        }
    }
}