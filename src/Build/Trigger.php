<?php

declare(strict_types=1);

namespace Duyler\EventBus\Build;

use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Formatter\IdFormatter;
use JsonSerializable;
use Override;
use UnitEnum;

final readonly class Trigger implements JsonSerializable
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

    #[Override]
    public function jsonSerialize(): mixed
    {
        return [
            'subjectId' => $this->subjectId,
            'actionId' => $this->actionId,
            'status' => $this->status->value,
        ];
    }
}
