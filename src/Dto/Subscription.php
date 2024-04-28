<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Dto;

use Duyler\ActionBus\Enum\ResultStatus;
use Duyler\ActionBus\Formatter\IdFormatter;
use UnitEnum;

readonly class Subscription
{
    public string $subjectId;
    public string $actionId;

    public function __construct(
        string|UnitEnum $subjectId,
        string|UnitEnum $actionId,
        public ResultStatus $status = ResultStatus::Success,
    ) {
        $this->subjectId = IdFormatter::format($subjectId);
        $this->actionId = IdFormatter::format($actionId);
    }
}
