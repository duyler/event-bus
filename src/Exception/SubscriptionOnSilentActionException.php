<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class SubscriptionOnSilentActionException extends Exception
{
    public function __construct(string $actionId, string $silentActionId)
    {
        parent::__construct('Action ' . $actionId . 'can not be subscribed on silent action ' . $silentActionId);
    }
}
