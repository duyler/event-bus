<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Exception;

use Exception;

class EventHandlersNotFoundException extends Exception
{
    public function __construct(string $eventId)
    {
        parent::__construct(sprintf('Event handlers not found for event "%s"', $eventId));
    }
}
