<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

use Duyler\EventBus\Enum\ResultStatus;

readonly class Trigger
{
    public function __construct(
        public string $id,
        public ResultStatus $status,
        public object|null $data = null,
        public string|null $contract = null,
    ) {}
}
