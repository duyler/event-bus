<?php

declare(strict_types=1);

namespace Duyler\EventBus\Actor\Exception;

use Exception;

class ActorHandlerMustBeCallableException extends Exception
{
    public function __construct()
    {
        parent::__construct('Actor handler must be invokable object or closure type');
    }
}
