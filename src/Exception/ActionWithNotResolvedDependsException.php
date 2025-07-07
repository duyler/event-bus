<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class ActionWithNotResolvedDependsException extends Exception
{
    public function __construct(string $typeId, string $actionId)
    {
        $message = 'No action provides a type ' . $typeId . ' for action ' . $actionId;
        parent::__construct($message);
    }
}
