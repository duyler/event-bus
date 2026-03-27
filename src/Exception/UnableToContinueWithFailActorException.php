<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class UnableToContinueWithFailActorException extends Exception
{
    public function __construct(string $actorId)
    {
        parent::__construct('Unable to push actor ' . $actorId . ' with fail required actors');
    }
}
