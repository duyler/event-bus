<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class ActorAlreadyDefinedException extends Exception
{
    public function __construct(string $actorId)
    {
        parent::__construct('Actor with id ' . $actorId . ' already defined');
    }
}
