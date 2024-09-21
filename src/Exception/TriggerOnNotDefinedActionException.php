<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Duyler\EventBus\Build\Trigger;
use Exception;

class TriggerOnNotDefinedActionException extends Exception
{
    public function __construct(Trigger $trigger)
    {
        parent::__construct('Action ' . $trigger->actionId . ' not defined in the bus');
    }
}
