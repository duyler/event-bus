<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Build;

use Duyler\ActionBus\Enum\ResultStatus;
use Duyler\ActionBus\Formatter\IdFormatter;
use UnitEnum;

final readonly class Subscription
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
