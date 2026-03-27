<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class ActorReturnValueExistsException extends Exception
{
    public function __construct(string $actorId)
    {
        $message = 'Actor ' . $actorId . ' set as not return value, but returned value given';
        parent::__construct($message);
    }
}
