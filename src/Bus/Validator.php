<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Bus;

use Duyler\ActionBus\BusConfig;
use Duyler\ActionBus\Exception\CircularCallActionException;
use Duyler\ActionBus\Exception\ConsecutiveRepeatedActionException;

final class Validator
{
    public function __construct(
        private Log $log,
        private BusConfig $config,
    ) {}

    /**
     * @throws ConsecutiveRepeatedActionException
     * @throws CircularCallActionException
     */
    public function validateCompleteAction(CompleteAction $completeAction): void
    {
        $actionId = $completeAction->action->id . '.' . $completeAction->result->status->value;

        $mainEventLog = $this->log->getMainLog();
        $repeatedEventLog = $this->log->getRepeatedLog();

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
