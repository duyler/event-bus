<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class ActorNotAllowExternalAccessException extends Exception
{
    public function __construct(string $actorId)
    {
        parent::__construct('Actor ' . $actorId . ' does not allow external access');
    }
}
