<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

use Duyler\EventBus\Enum\ResultStatus;

readonly class Subscription
{
    public function __construct(
        public string       $subjectId,
        public string       $actionId,
        public ResultStatus $status = ResultStatus::Success,
    ) {
    }
}
