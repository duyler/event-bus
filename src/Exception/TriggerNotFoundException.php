<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Duyler\EventBus\Build\Trigger;
use Exception;

class TriggerNotFoundException extends Exception
{
    public function __construct(Trigger $trigger)
    {
        parent::__construct('Trigger not found: ' . $trigger->actionId . '@' . $trigger->subjectId);
    }
}
