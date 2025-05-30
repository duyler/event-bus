<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

use Duyler\EventBus\Enum\ResultStatus;

final readonly class Result
{
    private function __construct(public ResultStatus $status, public ?object $data = null) {}

    public static function success(?object $data = null): Result
    {
        return new self(ResultStatus::Success, $data);
    }

    public static function fail(): Result
    {
        return new self(ResultStatus::Fail);
    }
}
