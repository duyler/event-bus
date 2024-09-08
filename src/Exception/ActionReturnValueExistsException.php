<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class ActionReturnValueExistsException extends Exception
{
    public function __construct(string $actionId)
    {
        $message = 'Action ' . $actionId . ' set as not return value, but returned value given';
        parent::__construct($message);
    }
}
