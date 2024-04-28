<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Action\Exception;

use Exception;

class InvalidArgumentFactoryException extends Exception
{
    public function __construct(string $class)
    {
        $message = sprintf('Argument factory %s must be callable', $class);
        parent::__construct($message);
    }
}
