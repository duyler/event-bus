<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Dto;

use Duyler\ActionBus\Formatter\IdFormatter;
use UnitEnum;

readonly class ActionResultSubstitution
{
    public string $actionId;

    public function __construct(
        string|UnitEnum $actionId,
        public string $requiredContract,
        public object $substitution,
    ) {
        $this->actionId = IdFormatter::format($actionId);
    }
}
