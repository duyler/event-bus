<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class DispatchedEventNotDefinedException extends Exception
{
    public function __construct(string $event)
    {
        parent::__construct(
            'Listen event ' . $event . ' not defined in the bus',
        );
    }
}
