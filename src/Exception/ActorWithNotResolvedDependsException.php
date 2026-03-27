<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class ActorWithNotResolvedDependsException extends Exception
{
    public function __construct(string $typeId, string $actorId)
    {
        $message = 'No actor provides a type ' . $typeId . ' for actor ' . $actorId;
        parent::__construct($message);
    }
}
