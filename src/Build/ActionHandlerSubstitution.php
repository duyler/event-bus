<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Build;

use Closure;
use Duyler\ActionBus\Formatter\IdFormatter;
use UnitEnum;

final readonly class ActionHandlerSubstitution
{
    public string $actionId;

    public function __construct(
        string|UnitEnum $actionId,
        public string|Closure $handler,
        public array $bind = [],
        public array $providers = [],
    ) {
        $this->actionId = IdFormatter::toString($actionId);
    }
}
