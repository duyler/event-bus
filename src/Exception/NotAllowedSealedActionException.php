<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class NotAllowedSealedActionException extends Exception
{
    public function __construct(string $actionId, string $sealedActionId)
    {
        parent::__construct('Action ' . $actionId . ' cannot be sealed to ' . $sealedActionId);
    }
}
