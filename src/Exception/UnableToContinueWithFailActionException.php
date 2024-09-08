<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class UnableToContinueWithFailActionException extends Exception
{
    public function __construct(string $actionId)
    {
        parent::__construct('Unable to push action ' . $actionId . ' with fail required actions');
    }
}
