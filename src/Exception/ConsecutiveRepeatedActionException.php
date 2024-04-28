<?php

namespace Duyler\ActionBus\Exception;

use Exception;

class ConsecutiveRepeatedActionException extends Exception
{
    public function __construct(string $actionName, string $statue)
    {
        $message = 'The action "' . $actionName . '" consecutive repeated with status"' . $statue . '"';

        parent::__construct($message);
    }
}
