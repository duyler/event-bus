<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Exception;

use Exception;

class ContractForDataNotReceivedException extends Exception
{
    public function __construct(string $subjectId)
    {
        $message = $subjectId . ' with data, but contract for data is not received';
        parent::__construct($message);
    }
}
