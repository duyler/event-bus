<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Exception;

use Exception;

class EventNotDefinedException extends Exception
{
    public function __construct(string $event, string $actionId)
    {
        parent::__construct(
            'Listen event ' . $event . ' for action ' . $actionId . ' not defined in the bus',
        );
    }
}
