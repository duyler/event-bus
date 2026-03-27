<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class EventNotDefinedException extends Exception
{
    public function __construct(string $event, string $actorId)
    {
        parent::__construct(
            'Listen event ' . $event . ' for actor ' . $actorId . ' not defined in the bus',
        );
    }
}
