<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Exception\CircularCallActionException;

final readonly class Validator
{
    public function __construct(
        private State $state,
        private BusConfig $config,
    ) {}

    /**
     * @throws CircularCallActionException
     */
    public function validateCompleteAction(CompleteAction $completeAction): void
    {
        $mainEventLog = $this->state->getMainLog();
        $repeatedEventLog = $this->state->getRepeatedLog();

        if (false === $this->config->allowCircularCall) {
            if (count($mainEventLog) === count($repeatedEventLog)) {
                throw new CircularCallActionException($completeAction->action->getId(), (string) end($mainEventLog));
            }
        }
    }
}
