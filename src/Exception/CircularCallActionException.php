<?php

namespace Duyler\EventBus\Exception;

use Exception;

class CircularCallActionException extends Exception
{
    public function __construct(string $actionName, string $requestedAction)
    {
        $message = 'Action "' . $actionName . '" has a circular required in action "' . $requestedAction . '"';

        parent::__construct($message);
    }
}
