<?php

declare(strict_types=1);

namespace Duyler\EventBus\Build;

use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Formatter\IdFormatter;
use UnitEnum;

final readonly class Trigger
{
    public string $subjectId;
    public string $actionId;

    public function __construct(
        string|UnitEnum $subjectId,
        string|UnitEnum $actionId,
        public ResultStatus $status = ResultStatus::Success,
    ) {
        $this->subjectId = IdFormatter::toString($subjectId);
        $this->actionId = IdFormatter::toString($actionId);
    }
}
