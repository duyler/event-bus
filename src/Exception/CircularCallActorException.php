<?php

namespace Duyler\EventBus\Exception;

use Exception;

class CircularCallActorException extends Exception
{
    public function __construct(string $actorName, string $requestedActor)
    {
        $message = 'Actor "' . $actorName . '" has a circular required in actor "' . $requestedActor . '"';

        parent::__construct($message);
    }
}
