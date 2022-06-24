<?php

declare(strict_types=1);

namespace Konveyer\EventBus\DTO;

use Konveyer\EventBus\Enum\ResultStatus;

class Subscribe
{
    public function __construct(
        public readonly string $subject,
        public readonly string $actionFullName,
        public readonly ResultStatus $status = ResultStatus::POSITIVE,
        public readonly bool $silent = false
    ) {

    }
}
