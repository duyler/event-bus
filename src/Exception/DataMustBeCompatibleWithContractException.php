<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class DataMustBeCompatibleWithContractException extends Exception
{
    public function __construct(string $subjectId, string $contract)
    {
        $message = $subjectId . ' data must be compatible with ' . $contract;
        parent::__construct($message);
    }
}
