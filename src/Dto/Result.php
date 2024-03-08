<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

use Duyler\EventBus\Enum\ResultStatus;

readonly class Result
{
    public ResultStatus $status;
    public ?object $data;

    public function __construct(ResultStatus $status, ?object $data = null)
    {
        $this->status = $status;
        $this->data = $data;
    }
}
