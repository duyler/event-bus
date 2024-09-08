<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class ActionNotAllowExternalAccessException extends Exception
{
    public function __construct(string $actionId)
    {
        parent::__construct('Action ' . $actionId . ' does not allow external access');
    }
}
