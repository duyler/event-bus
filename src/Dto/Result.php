<?php

declare(strict_types=1);

namespace Konveyer\EventBus\DTO;

use Konveyer\EventBus\Enum\ResultStatus;

class Result
{
    public readonly ResultStatus $status;
    public readonly object|null $data;

    public function __construct(ResultStatus $status, mixed $data = null)
    {
        $this->status = $status;
        $this->data = $data;
    }
}
