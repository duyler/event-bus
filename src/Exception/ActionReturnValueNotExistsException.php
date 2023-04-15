<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class ActionReturnValueNotExistsException extends Exception
{
    public function __construct(string $actionId)
    {
        $message = 'Action ' . $actionId . ' set as return value, but value is not returned';
        parent::__construct($message);
    }
}
