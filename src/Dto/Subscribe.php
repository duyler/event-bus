<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

use Duyler\EventBus\Enum\ResultStatus;

class Subscribe
{
    public function __construct(
        public readonly string $subjectId,
        public readonly string $actionId,
        public readonly ResultStatus $status = ResultStatus::Success,
    ) {
    }
}
