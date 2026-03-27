<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class ActorReturnValueMustBeTypeObjectException extends Exception
{
    public function __construct(string $actorId, mixed $data)
    {
        $message = 'Actor ' . $actorId . ' return value must be type object ' . gettype($data) . ' given';
        parent::__construct($message);
    }
}
