<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

use Duyler\EventBus\Enum\ResultStatus;

final readonly class Result
{
    public ResultStatus $status;
    public ?object $data;

    private function __construct(ResultStatus $status, ?object $data = null)
    {
        $this->status = $status;
        $this->data = $data;
    }

    public static function success(?object $data = null): Result
    {
        return new self(ResultStatus::Success, $data);
    }

    public static function fail(): Result
    {
        return new self(ResultStatus::Fail);
    }
}
