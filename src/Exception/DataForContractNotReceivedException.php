<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class DataForContractNotReceivedException extends Exception
{
    public function __construct(string $subjectId, string $contract)
    {
        $message = $subjectId . ' set as contract ' . $contract . ', but data for contract is not received';
        parent::__construct($message);
    }
}
