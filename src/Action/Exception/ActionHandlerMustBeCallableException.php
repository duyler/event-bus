<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action\Exception;

use Exception;

class ActionHandlerMustBeCallableException extends Exception
{
    public function __construct()
    {
        parent::__construct('Action handler must be invokable object or closure type');
    }
}
