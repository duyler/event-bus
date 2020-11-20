<?php

namespace Jine\EventBus\Exception;

use Exception;

class InterfaceMapNotFoundException extends Exception
{
    public function __construct(string $interfaceName)
    {
        $message = 'Interface map not found for ' . $interfaceName;
    
        parent::__construct($message);
    }
} 
 
