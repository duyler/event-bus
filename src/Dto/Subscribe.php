<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

use Duyler\EventBus\Enum\ResultStatus;

class Subscribe
{
    public function __construct(
        public readonly string $subject,
        public readonly string $actionFullName,
        public readonly ResultStatus $status = ResultStatus::Success,
        public readonly bool $silent = false
    ) {
    }
}
