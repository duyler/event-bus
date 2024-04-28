<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Exception;

use Exception;

class ActionAlreadyDefinedException extends Exception
{
    public function __construct(string $actionId)
    {
        parent::__construct('Action with id ' . $actionId . ' already defined');
    }
}
