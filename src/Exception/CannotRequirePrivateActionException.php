<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class CannotRequirePrivateActionException extends Exception
{
    public function __construct(string $action, string $requiredAction)
    {
        parent::__construct(
            sprintf('Action %s cannot require private action %s', $action, $requiredAction)
        );
    }
}
