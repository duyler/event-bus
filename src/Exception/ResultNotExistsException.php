<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class ResultNotExistsException extends Exception
{
    public function __construct(string $actorId)
    {
        parent::__construct('Actor or event result for ' . $actorId . ' does not exist');
    }
}
