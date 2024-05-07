<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State;

use UnitEnum;

readonly class Suspend
{
    public function __construct(
        public string|UnitEnum $actionId,
        public mixed $value,
    ) {}
}
