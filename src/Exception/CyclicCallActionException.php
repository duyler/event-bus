<?php

namespace Konveyer\EventBus\Exception;

use Exception;

class CyclicCallActionException extends Exception
{
    public function __construct(string $actionName, string $callingAction)
    {
        $message = 'The event "' . $actionName . '" has a cyclic call action "' . $callingAction . '"';
    
        parent::__construct($message);
    }
} 
