<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class ActionCoroutineNotSetException extends Exception
{
    public function __construct(string $actionId)
    {
        $message = 'Coroutine for action ' . $actionId . ' not set. Suspend state nothing handled';
        parent::__construct($message);
    }
}
