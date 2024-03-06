<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class ActionReturnValueMustBeCompatibleException extends Exception
{
    public function __construct(string $actionId, string $contract)
    {
        $message = 'Action ' . $actionId . ' return value, must be compatible with ' . $contract;
        parent::__construct($message);
    }
}
