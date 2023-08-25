<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class ActionReturnValueWillBeCompatibleException extends Exception
{
    public function __construct(string $actionId, string $contract)
    {
        $message = 'Action ' . $actionId . ' return value, will be compatible with ' . $contract;
        parent::__construct($message);
    }
}
