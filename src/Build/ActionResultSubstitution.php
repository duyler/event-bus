<?php

declare(strict_types=1);

namespace Duyler\EventBus\Build;

use Duyler\EventBus\Formatter\IdFormatter;
use UnitEnum;

final readonly class ActionResultSubstitution
{
    public string $actionId;
    public string $requiredActionId;

    public function __construct(
        string|UnitEnum $actionId,
        string|UnitEnum $requiredActionId,
        public object $substitution,
    ) {
        $this->actionId = IdFormatter::toString($actionId);
        $this->requiredActionId = IdFormatter::toString($requiredActionId);
    }
}
