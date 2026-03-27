<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Exception\CircularCallActorException;

use function count;
use function end;

final readonly class Validator
{
    public function __construct(
        private State $state,
        private BusConfig $config,
    ) {}

    /**
     * @throws CircularCallActorException
     */
    public function validateCompleteActor(CompleteActor $completeActor): void
    {
        $mainEventLog = $this->state->getMainLog();
        $repeatedEventLog = $this->state->getRepeatedLog();

        if (false === $this->config->allowCircularCall) {
            if (count($mainEventLog) === count($repeatedEventLog)) {
                throw new CircularCallActorException($completeActor->actor->getId(), (string) end($mainEventLog));
            }
        }
    }
}
