<?php

namespace Duyler\EventBus\Exception;

use Exception;

class CircularCallActionException extends Exception
{
    public function __construct(string $actionName, string $callingAction)
    {
        $message = 'The event "' . $actionName . '" has a cyclic call after action "' . $callingAction . '"';
    
        parent::__construct($message);
    }
} 
