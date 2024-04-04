<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class TriggerHandlersNotFoundException extends Exception
{
    public function __construct(string $triggerId)
    {
        parent::__construct(sprintf('Trigger handlers not found for trigger "%s"', $triggerId));
    }
}
