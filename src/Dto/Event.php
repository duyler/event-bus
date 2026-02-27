<?php

declare(strict_types=1);

namespace Duyler\EventBus\Dto;

use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Formatter\IdFormatter;
use UnitEnum;

final readonly class Event
{
    public string $id;

    public function __construct(
        string|UnitEnum $id,
        ResultStatus $status = ResultStatus::Success,
        public ?object $data = null,
    ) {
        $this->id = IdFormatter::toString($id) . IdFormatter::DELIMITER . $status->value;
    }
}
