<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

use Duyler\EventBus\Enum\ResultStatus;

class Result
{
    public readonly ResultStatus $status;
    public readonly object|null $data;

    public function __construct(ResultStatus $status, object $data = null)
    {
        $this->status = $status;
        $this->data = $data;
    }
}
