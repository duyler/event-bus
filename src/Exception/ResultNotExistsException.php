<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class ResultNotExistsException extends Exception
{
    public function __construct(string $actionId)
    {
        parent::__construct('Action or trigger result for ' . $actionId . ' does not exist');
    }
}
