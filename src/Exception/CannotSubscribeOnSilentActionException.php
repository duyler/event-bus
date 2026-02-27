<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class CannotSubscribeOnSilentActionException extends Exception
{
    public function __construct(string $action, string $subscriptionAction)
    {
        parent::__construct(
            sprintf('Action %s cannot subscribe on silent action %s', $action, $subscriptionAction),
        );
    }
}
