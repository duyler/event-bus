<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class CannotSubscribeOnRequiredActionException extends Exception
{
    public function __construct(string $action, string $subscriptionAction)
    {
        parent::__construct(
            sprintf('Action %s cannot subscribe and require at the same time on action %s', $action, $subscriptionAction),
        );
    }
}
