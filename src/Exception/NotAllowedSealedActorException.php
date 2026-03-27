<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class NotAllowedSealedActorException extends Exception
{
    public function __construct(string $actorId, string $sealedActorId)
    {
        parent::__construct('Actor ' . $actorId . ' cannot be sealed to ' . $sealedActorId);
    }
}
