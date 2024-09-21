<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Duyler\EventBus\Build\Trigger;
use Exception;

class TriggerAlreadyDefinedException extends Exception
{
    public function __construct(Trigger $trigger)
    {
        parent::__construct(
            sprintf(
                'Trigger with action id %s, status %s, and subject id %s already defined',
                $trigger->actionId,
                $trigger->status->value,
                $trigger->subjectId,
            ),
        );
    }
}
