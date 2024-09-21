<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class TriggerOnSilentActionException extends Exception
{
    public function __construct(string $actionId, string $silentActionId)
    {
        parent::__construct('Action ' . $actionId . 'can not be triggered on silent action ' . $silentActionId);
    }
}
