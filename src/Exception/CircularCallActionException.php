<?php

namespace Duyler\ActionBus\Exception;

use Exception;

class CircularCallActionException extends Exception
{
    public function __construct(string $actionName, string $statue)
    {
        $message = 'The event "' . $actionName . '" has a cyclic call after action "' . $statue . '"';

        parent::__construct($message);
    }
}
