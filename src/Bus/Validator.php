<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Exception\CircularCallActionException;
use Duyler\EventBus\Exception\ConsecutiveRepeatedActionException;

final class Validator
{
    public function __construct(
        private State $state,
        private BusConfig $config,
    ) {}

    /**
     * @throws ConsecutiveRepeatedActionException
     * @throws CircularCallActionException
     */
    public function validateCompleteAction(CompleteAction $completeAction): void
    {
        $actionId = $completeAction->action->id . '.' . $completeAction->result->status->value;

        $mainEventLog = $this->state->getMainLog();
        $repeatedEventLog = $this->state->getRepeatedLog();

        if (false === $this->config->allowCircularCall) {
            if (end($repeatedEventLog) === $actionId && false === $completeAction->action->repeatable) {
                throw new ConsecutiveRepeatedActionException(
                    $completeAction->action->id,
                    $completeAction->result->status->value,
                );
            }

            if (count($mainEventLog) === count($repeatedEventLog)) {
                throw new CircularCallActionException($completeAction->action->id, (string) end($mainEventLog));
            }
        }
    }
}
