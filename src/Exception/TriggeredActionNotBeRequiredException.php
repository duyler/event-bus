<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class TriggeredActionNotBeRequiredException extends Exception
{
    public function __construct(string $actionId, string $triggeredActionId)
    {
        parent::__construct('Action ' . $actionId . ' cannot be required triggered action ' . $triggeredActionId);
    }
}
