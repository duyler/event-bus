<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action\Exception;

use Exception;

class ActionReturnValueMustBeTypeObjectException extends Exception
{
    public function __construct(string $actionId, mixed $data)
    {
        $message = 'Action ' . $actionId . ' return value must be type object ' . gettype($data) . ' given';
        parent::__construct($message);
    }
}
