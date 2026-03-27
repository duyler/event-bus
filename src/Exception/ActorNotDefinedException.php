<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class ActorNotDefinedException extends Exception
{
    public function __construct(string $subject)
    {
        parent::__construct('Required actor ' . $subject . ' not defined in the bus');
    }
}
