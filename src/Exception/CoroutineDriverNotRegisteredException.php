<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class CoroutineDriverNotRegisteredException extends Exception
{
    public function __construct(string $driver)
    {
        $message = 'Coroutine driver ' . $driver . ' is not registered in the bus';
        parent::__construct($message);
    }
}
