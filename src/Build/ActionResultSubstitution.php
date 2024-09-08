<?php

declare(strict_types=1);

namespace Duyler\EventBus\Build;

use Duyler\EventBus\Formatter\IdFormatter;
use UnitEnum;

final readonly class ActionResultSubstitution
{
    public string $actionId;

    public function __construct(
        string|UnitEnum $actionId,
        public string $requiredContract,
        public object $substitution,
    ) {
        $this->actionId = IdFormatter::toString($actionId);
    }
}
