<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Exception;

use Exception;

class ActionNotDefinedException extends Exception
{
    public function __construct(string $subject)
    {
        parent::__construct('Required action ' . $subject . ' not defined in the bus');
    }
}
